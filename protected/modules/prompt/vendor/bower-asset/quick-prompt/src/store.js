/**
 * Copyright 2023 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import {create} from 'zustand'
import shuffle from 'lodash.shuffle'
import {
  GAME_START_REQUEST,
  GAME_START_RESPONSE,
  FORBIDDEN_WORD_RESPONSE,
  NEW_WORD_PREFIX,
  USER_TURN_SUFFIX,
  SKIP_REQUEST,
  SKIP_RESPONSE
} from './lib/priming'
import {v4 as uuid} from 'uuid'
import * as api from './lib/api'
import * as speech from './lib/speech'
import * as sound from './lib/sound'
import error_responses from './lib/error_responses'
import {roundSeconds, matchDisallowedWords, checkIsAndroid} from './lib/utils'
import allWords from './lib/words'

const useStore = create((set, get) => ({
  inputActive: false,
  speechCharIndex: 0,
  dialogue: [], // Dialogue that is displayed on-screen
  modelDialogue: [], // Dialogue that gets sent to the model
  gamePrompt: {},
  words: [],
  currentGuess: '',
  guessedWords: [],
  wordIndex: 0,
  score: 0,
  secondsLeft: roundSeconds,
  tickInt: null,
  didWin: false,
  didSkip: false,
  addNewWordPrefix: false,
  badInput: false,
  gameStarted: false,
  timerStarted: false,
  gameOver: false,
  muted: false,
  help: false,
  computerVoice: false,
  inputDisabled: false,
  paused: false,
  isAndroid: checkIsAndroid(),
  actions: {
    playSound: fileName => {
      const {muted} = get()
      if (!muted) sound.playSound(fileName)
    },
    /**
     * Fetch words from src/lib/words.js.
     * The resulting `words` array contains three random words from each of the
     * difficulty levels (easy, medium, hard) in order of ascending difficulty,
     * followed by all remaining words in random order.
     */
    fetchWords: () => {
      const {easy_words, medium_words, hard_words} = allWords
      const easy = shuffle(easy_words)
      const medium = shuffle(medium_words)
      const hard = shuffle(hard_words)

      const words = [
        ...easy.splice(0, 3),
        ...medium.splice(0, 3),
        ...hard.splice(0, 3),
        ...shuffle([...easy, ...medium, ...hard])
      ]

      set({
        words
      })
    },
    /**
     * Set `secondsLeft` to 0, which will trigger the end of the game.
     */
    stopGame: ({showStart} = {showStart: false}) => {
      const {actions} = get()

      setTimeout(actions.tick, 1)
      set({
        secondsLeft: 0,
        gameStarted: !showStart
      })
    },
    /**
     * Select a new word for the AI to guess.
     *
     * @param {boolean} reset If true, re-fetch the words.
     */
    startRound: reset => {
      set(() => {
        const {
          words,
          dialogue,
          modelDialogue,
          guessedWords,
          score,
          wordIndex,
          actions,
          secondsLeft,
          timerStarted,
          didWin
        } = get()

        if (reset) {
          actions.fetchWords()
        }

        return {
          gameStarted: true,
          wordIndex: reset ? 0 : wordIndex + 1,
          didWin: false,
          didSkip: false,
          gameOver: false,
          guessedWords: reset ? [] : guessedWords,
          dialogue:
            reset || dialogue.length === 0
              ? [{text: GAME_START_RESPONSE, id: uuid(), isModel: true}]
              : dialogue,
          modelDialogue:
            reset || modelDialogue.length === 0
              ? [
                  {text: GAME_START_REQUEST, id: uuid(), isModel: true},
                  {text: GAME_START_RESPONSE, id: uuid(), isModel: false}
                ]
              : modelDialogue,
          gamePrompt: words[wordIndex],
          score: reset ? 0 : score,
          secondsLeft: reset ? roundSeconds : secondsLeft,
          timerStarted: reset ? false : timerStarted
        }
      })
    },
    startTimer: () => {
      const {actions, tickInt, timerStarted} = get()
      if (timerStarted) return

      clearTimeout(tickInt)

      set({
        secondsLeft: roundSeconds,
        tickInt: setTimeout(actions.tick, 1),
        timerStarted: true
      })
    },
    /**
     * Perform all steps necessary to complete a user turn.
     * 
     * First, check the passed user hint against the target word and list of forbidden words.
     * Rules are as follows:
     * - The user hint may not contain the target word.
     * - The user hint may not contain a substring of the target word.
     *  (For example, if the target word is "earring", the user hint may not contain "ear" or "ring")
     * - The user hint may not contain any of the forbidden words.
     * 
     * If the passed string passes the above checks, add the string to the dialogue and call the model.
     * Otherwise, add the passed string (as the user turn) followed by FORBIDDEN_WORD_RESPONSE (as the model turn)
     * to the dialogue and move on to the next word (do not call the model).
     *
     * @param {string} text The hint provided by the user to help the AI guess the target word.
     */
    addHumanTurn: async text => {
      const {dialogue, modelDialogue, actions, gamePrompt, addNewWordPrefix} =
        get()
      const turnText = text.trim()

      const matches = matchDisallowedWords(
        gamePrompt,
        turnText.split(/[\W|\s]+/g).filter(Boolean)
      )

      if (matches.length > 0) {
        actions.setBadInput(matches)

        setTimeout(() => {
          const {secondsLeft, dialogue} = get()

          if (secondsLeft === 0) return

          actions.setBadInput(false)
          actions.startRound()

          // Add the forbidden word response to the dialogue that gets sent to the model
          set({
            dialogue: [
              ...dialogue,
              {id: uuid(), text: FORBIDDEN_WORD_RESPONSE, isModel: true}
            ]
          })
        }, 3000)

        actions.playSound('sounds/forbidden-word.mp3')

        // Sort matches by their indicies in the passed `text` so the words in `forbiddenPopup` appear in the correct order
        matches.sort((a, b) => {
          const aIndex = text.indexOf(a)
          const bIndex = text.indexOf(b)
          return aIndex - bIndex
        })

        set({
          modelDialogue: [
            ...modelDialogue,
            // Add the user text to the dialogue that gets sent to the model
            {
              id: uuid(),
              text: addNewWordPrefix
                ? `${NEW_WORD_PREFIX} ${turnText}`
                : turnText
            },
            // Add the forbidden word response to the dialogue that gets sent to the model
            {
              id: uuid(),
              text: FORBIDDEN_WORD_RESPONSE
            }
          ],
          addNewWordPrefix: false
        })

        const re = new RegExp(`(${matches.join('|')})`, 'g')

        // Add the marked user text to the dialogue that is displayed on-screen
        set({
          dialogue: [
            ...dialogue,
            {
              id: uuid(),
              text: turnText
                .replace(re, '<mark class="forbidden">$1</mark>')
                .trim()
            }
          ]
        })

        // If the user text does not passed the disallowed word checks, do not call the model
        return
      }

      const modelDialogueText = addNewWordPrefix
        ? `${NEW_WORD_PREFIX} ${turnText} ${USER_TURN_SUFFIX}`
        : `${turnText} ${USER_TURN_SUFFIX}`

      set({
        // Add the user text to the dialogue that gets sent to the model
        modelDialogue: [
          ...modelDialogue,
          {
            id: uuid(),
            text: modelDialogueText
          }
        ],
        // Add the user text to the dialogue that is displayed on-screen
        dialogue: [
          ...dialogue,
          {
            id: uuid(),
            text: turnText
          }
        ],
        addNewWordPrefix: false
      })

      // Send the updated dialogue to the model
      await actions.addAITurn()
    },
    speakDialogue: async modelText => {
      const {computerVoice, muted} = get()

      if (muted || !computerVoice) return

      // If computerVoice is true, get the speech synthesis utterance
      const audio = computerVoice && speech.getUtterance(modelText, 'Fred')

      if (!muted && computerVoice) {
        set({speechCharIndex: 0})
        audio.onboundary = event => {
          set({speechCharIndex: event.charIndex})
        }

        await speech.playSpeech(audio)
      }
    },
    /**
     * Check the model's guess against the target word.
     */
    testGuess: async () => {
      const {currentGuess, gamePrompt} = get()

      if (
        currentGuess.trim().toLowerCase() ===
        gamePrompt.word.trim().toLowerCase()
      ) {
        const {score, guessedWords, gamePrompt, actions} = get()

        setTimeout(() => {
          actions.playSound('sounds/point-get.mp3')
        }, 900)

        set({didWin: true, score: score + 1, addNewWordPrefix: true})
        guessedWords.push(gamePrompt)
        setTimeout(actions.startRound, 2000)
      }
    },
    /**
     * Perform all steps necessary to complete a model turn.
     */
    addAITurn: async () => {
      const {dialogue, modelDialogue, actions, currentGuess} = get()

      actions.pause()

      // Send the conversation to the API to have the model generate a response
      const modelResponse = await api.performTurn(
        modelDialogue.map(({text}) => text)
      )

      const modelText =
        modelResponse.text ||
        (modelResponse.error &&
          error_responses[Math.floor(Math.random() * error_responses.length)])

      // If there is a guess in the model response, save it to state (the model will enclose guessed words within [square brackets])
      const guessMatch = modelText.toLowerCase().match(/\[([\w\s]+)\]/)
      const guess = guessMatch && guessMatch[1]

      // Add the model response to the dialogue
      set(() => {
        return {
          dialogue: [...dialogue, {id: uuid(), text: modelText, isModel: true}],
          modelDialogue: [
            ...modelDialogue,
            {id: uuid(), text: modelText, isModel: true}
          ],
          currentGuess: guess || currentGuess
        }
      })

      await actions.speakDialogue(modelText)

      set({
        speechCharIndex: 10000
      })

      actions.resume()

      if (guess) actions.testGuess()
    },
    setMute: muted => set(() => ({muted})),
    setHelp: help => {
      const {actions, gameOver} = get()
      set(() => {
        if (help || gameOver) {
          return {
            help,
            paused: help
          }
        }

        return {
          help,
          paused: help,
          tickInt: setTimeout(actions.tick, 1000)
        }
      })
    },
    setInputActive: inputActive => set(() => ({inputActive})),
    setBadInput: badInput => set(() => ({badInput})),
    setDidSkip: didSkip => {
      const {modelDialogue, dialogue} = get()

      const skipMessages = [
        {
          id: uuid(),
          text: SKIP_REQUEST,
          isModel: false
        },
        {
          id: uuid(),
          text: SKIP_RESPONSE,
          isModel: true
        }
      ]

      set({
        didSkip,
        modelDialogue: [...modelDialogue, ...skipMessages],
        dialogue: [...dialogue, ...skipMessages]
      })

      set(() => ({didSkip}))
    },
    // If computerVoice is turned on, set speechCharIndex to 10000 so that the whole text is visible
    setComputerVoice: computerVoice =>
      set(() => ({computerVoice, speechCharIndex: 10000})),
    pause: () => set({inputDisabled: true, paused: true}),
    resume: () => {
      const {actions} = get()
      set({
        inputDisabled: false,
        paused: false,
        tickInt: setTimeout(actions.tick, 0)
      })
    },
    tick: () => {
      const {secondsLeft, actions, didWin, paused, gameStarted, tickInt} = get()

      if (paused || !gameStarted) {
        return
      }

      if (!secondsLeft) {
        clearTimeout(tickInt)

        if (gameStarted) {
          actions.playSound('sounds/game-end.mp3')
        }
        actions.setBadInput(false)
        return set({gameOver: true})
      }

      const timeout = secondsLeft - 1 === 0 ? 0 : 1000

      set(() => ({
        // If `didWin` is true, pause the timer
        secondsLeft: didWin ? secondsLeft : secondsLeft - 1,
        tickInt: setTimeout(actions.tick, timeout)
      }))
    }
  }
}))

export const useActions = () => useStore(({actions}) => actions)
export const useDialogue = () => useStore(({dialogue}) => dialogue)
export const useGamePrompt = () => useStore(({gamePrompt}) => gamePrompt)
export const useWords = () => useStore(({words}) => words)
export const useDidWin = () => useStore(({didWin}) => didWin)
export const useDidSkip = () => useStore(({didSkip}) => didSkip)
export const useBadInput = () => useStore(({badInput}) => badInput)
export const useGameOver = () => useStore(({gameOver}) => gameOver)
export const useGameStarted = () => useStore(({gameStarted}) => gameStarted)
export const useTimerStarted = () => useStore(({timerStarted}) => timerStarted)
export const useScore = () => useStore(({score}) => score)
export const useGuessedWords = () => useStore(({guessedWords}) => guessedWords)
export const useMuted = () => useStore(({muted}) => muted)
export const useComputerVoice = () =>
  useStore(({computerVoice}) => computerVoice)
export const useHelp = () => useStore(({help}) => help)
export const useSecondsLeft = () => useStore(({secondsLeft}) => secondsLeft)
export const useInputDisabled = () =>
  useStore(({inputDisabled}) => inputDisabled)
export const useSpeechCharIndex = () =>
  useStore(({speechCharIndex}) => speechCharIndex)
export const useInputActive = () => useStore(({inputActive}) => inputActive)
export const useCurrentGuess = () => useStore(({currentGuess}) => currentGuess)
export const useIsAndroid = () => useStore(({isAndroid}) => isAndroid)
