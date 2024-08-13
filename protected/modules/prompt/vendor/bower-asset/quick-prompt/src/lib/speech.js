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

const speechSynth = window.speechSynthesis
let allVoices = speechSynth.getVoices()
let voices

speechSynth.onvoiceschanged = () => {
  allVoices = speechSynth.getVoices()
  voices = allVoices.filter(
    ({localService, lang}) => localService && lang.match(/^en/)
  )
}

export const checkVoiceSupport = () => {
  if (!voices) return false
  return voices.length > 0
}


// Text-to-speech requires a user interaction to work
// Call this function on page load when turning on computer voice in settings
export const initSpeechSynth = () =>
  speechSynth.speak(new SpeechSynthesisUtterance(''))

/**
 * Get a speech synthesis utterance.
 *
 * @param {string} text The text to speak.
 * @param {string} voice The name of the voice to use.
 * @returns {SpeechSynthesisUtterance}
 */
export const getUtterance = (text, voice) => {
  const t = text
    .trim()
    .replaceAll('[', '')
    .replaceAll(']', '')
    .split(' ')
    .map(word => word)
    .join(' ')

  const utterance = new SpeechSynthesisUtterance(t)
  const selectedVoice = voices.find(({name}) => name === voice) || voices[0]

  utterance.voice = selectedVoice

  return utterance
}

/**
 * Play a speech synthesis utterance.
 *
 * @param {SpeechSynthesisUtterance} utterance The utterance to play.
 * @returns {Promise}
 */
export const playSpeech = utterance => {
  speechSynth.speak(utterance)

  return new Promise(resolve => {
    utterance.onend = resolve
  }).catch(err => {
    console.log('[playSpeech error]', err)
  })
}

export const cancelSpeech = () => {
  speechSynth.cancel()
}
