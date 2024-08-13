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

import {useCallback, useEffect, useRef, useState, Fragment} from 'react'
import PropTypes from 'prop-types'
import c from 'classnames'
import BorderButton from '../../generic/borderButton/borderButton'
import Input from '../../generic/input/input'
import Card from '../../ui/card/card'
import {cancelSpeech} from '../../../lib/speech'
import {
  useActions,
  useDialogue,
  useGamePrompt,
  useInputDisabled,
  useComputerVoice,
  useSpeechCharIndex,
  useInputActive,
  useDidWin,
  useBadInput,
  useIsAndroid
} from '../../../store'
import {splitText} from '../../../lib/utils'
import styles from './gameScreen.module.scss'

const SplitScreenWord = ({active, word, onActive}) => {
  const splitScreenWordRef = useRef(null)
  useEffect(() => {
    if (!active) return

    onActive(splitScreenWordRef.current)
  }, [active])

  return (
    <span
      ref={splitScreenWordRef}
      className={c(active && 'active')}
      dangerouslySetInnerHTML={{__html: word}}
    />
  )
}

SplitScreenWord.propTypes = {
  active: PropTypes.bool,
  word: PropTypes.string,
  onActive: PropTypes.func
}

const GameScreen = () => {
  const gamePrompt = useGamePrompt()
  const computerVoice = useComputerVoice()
  const isAndroid = useIsAndroid()
  const {addHumanTurn, startRound, startTimer} = useActions()
  const dialogue = useDialogue()
  const didWin = useDidWin()
  const [inputVal, setInputVal] = useState('')

  const inputActive = useInputActive()
  const {setInputActive, setDidSkip} = useActions()

  const badInput = useBadInput()
  const inputDisabled = useInputDisabled()
  const speechCharIndex = useSpeechCharIndex()
  const dialogueRef = useRef()

  const onSubmit = () => {
    if (!inputVal.match(/\w/)) return

    addHumanTurn(inputVal)
    setInputVal('')
  }

  const onSkip = () => {
    setDidSkip(true)
    setTimeout(() => {
      startTimer()
      startRound()
      setInputVal('')
      document.querySelector(`.${styles.actions} input`).focus()
    }, 10)
  }

  const onInputChange = useCallback(
    event => {
      if ((badInput && true) || inputDisabled) return

      const value = event.target.value.trimStart()
      setInputVal(value)
      startTimer()
    },
    [startTimer, badInput, inputDisabled]
  )

  useEffect(() => {
    if (dialogueRef.current && !computerVoice)
      dialogueRef.current.scrollTo(0, dialogueRef.current.scrollHeight)
  }, [dialogue, inputDisabled])

  // stop talking on unmount
  useEffect(() => {
    return () => {
      if (!computerVoice) return

      cancelSpeech()
    }
  }, [])

  return (
    <div className={c(styles.gameScreen)}>
      <section className={c(styles.dialogue)}>
        <div className={styles.childWrapper}>
          <div ref={dialogueRef} className={styles.dialogueChildren}>
            <ul
              className={c(
                'container',
                'dialogueList',
                badInput && 'forbiddenWord'
              )}
            >
              {dialogue.map(({text, id, isModel}, i) => {
                return (
                  <Fragment key={id}>
                    {computerVoice &&
                    !isAndroid &&
                    isModel &&
                    (i === dialogue.length - 1 || dialogue.length === 1) ? ( // only split text if it's the last element in the array and there is more than 1 element
                      <li className={c(styles.isModel, 'wordByWord')}>
                        {splitText(text.toLowerCase()).map(
                          ({word, count}, j) => {
                            const _word = word
                              .replaceAll(
                                new RegExp(/\[([\w\s]+)\]/, 'g'),
                                '<mark class="guess">$1</mark>'
                              )
                              .replace(/[[\]]/g, '')
                            return (
                              <Fragment key={`${count}_${j}`}>
                                <SplitScreenWord
                                  key={`${count}_${j}`}
                                  active={speechCharIndex >= count}
                                  word={_word}
                                  onActive={el => {
                                    el.scrollIntoView({
                                      behavior: 'smooth',
                                      block: 'end',
                                      inline: 'nearest'
                                    })
                                  }}
                                />{' '}
                              </Fragment>
                            )
                          }
                        )}
                      </li>
                    ) : (
                      <li
                        className={c(isModel && styles.isModel)}
                        dangerouslySetInnerHTML={{
                          __html: isModel
                            ? text
                                .toLowerCase()
                                .replace(
                                  new RegExp(/\[([\w\s]+)\]/, 'g'),
                                  '<mark class="guess">$1</mark>'
                                )
                                .replace(/[[\]]/g, '')
                            : text
                        }}
                      />
                    )}
                  </Fragment>
                )
              })}
            </ul>
          </div>
        </div>
        <div className={c(styles.actions, badInput && 'badInput', 'container')}>
          <Input
            forceFocus={!didWin && !badInput}
            onSubmit={onSubmit}
            onInputChange={onInputChange}
            inputVal={inputVal}
            disabled={(badInput && true) || inputDisabled}
            onFocus={setInputActive}
          />
          <div className={c(styles.skipButton, inputActive && styles.hide)}>
            <BorderButton
              onClick={() => {
                onSkip()
              }}
            >
              <span className="emoji">⏩</span>
              &nbsp;{'Skip'}
            </BorderButton>
          </div>
        </div>
        <div className={c(styles.card, 'container')}>
          <Card disallowed={gamePrompt?.disallowed} />
        </div>
      </section>
    </div>
  )
}

export default GameScreen
