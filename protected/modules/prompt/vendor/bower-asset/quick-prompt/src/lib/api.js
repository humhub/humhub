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

import {post} from './firebase.config'
import {QUICKPROMPT_PROMPT_COMPONENTS} from './priming'

const MODEL_TEMPERATURE = 0.25
const NUM_CANDIDATE_RESPONSES = 8

/**
 * @typedef {Object} Message
 * @property {string|undefined} author The author of this Message (optional).
 * @property {string} content          The text content of the Message.
 * 
 * @typedef {Object} Example
 * @property {Message} input  An example of an input Message from the user.
 * @property {Message} output An example of what the model should output given the input.
 * 
 * @typedef {Object} MessagePrompt
 * @property {string|undefined} context     Context to steer model responses (optional).
 * @property {Example[]|undefined} examples Examples to further tune model responses (optional).
 * @property {Message[]} messages           The conversation, as an array of alterning user/model Messages.
 */

/**
 * Get a guess from the model.
 * 
 * @param {MessagePrompt} prompt The MessagePrompt to send to the model.
 * @param {number} temperature   The model temperature.
 * @returns A Promise object that, if fulfilled, returns an object that represents the model's response.
 */
const getGuess = async (prompt, temperature = MODEL_TEMPERATURE) => {
  try {
    // Call the PaLM API
    // For more info, see https://developers.generativeai.google/api/rest/generativelanguage/models/generateMessage
    const result = await post({
      model: 'chat-bison-001',
      method: 'generateMessage',
      prompt: prompt,
      temperature: temperature,
      candidateCount: NUM_CANDIDATE_RESPONSES
    })

    let candidateGuesses = []

    result.data.candidates.forEach(candidate => {
      candidateGuesses.push(
        candidate.content.split('\n')[0].split('\r')[0].trim()
      )
    })

    // Filter model responses that do not contain an item enclosed in square brackets,
    // as well as responses that contain more than one bracketed item
    candidateGuesses = candidateGuesses.filter(candidate => {
      return (candidate.match(/\[[^\[\]]*\]/gm) || []).length === 1
    })

    if (candidateGuesses.length > 0) {
      // Prioritize responses that contain a question mark so it feels like a conversation
      const candidateIndex = candidateGuesses.findIndex(candidate =>
        candidate.includes('?')
      )
      if (candidateIndex !== -1) {
        return {
          text: candidateGuesses[candidateIndex]
        }
      }
      return {
        text: candidateGuesses[0]
      }
    } else {
      return {error: 'No valid response was generated.'}
    }
  } catch (error) {
    console.error(error)
    return {error: error.message}
  }
}

/**
 * Perform the model's turn in a conversation between the user and the model.
 * 
 * @param {string[]} convo The conversation, as an array of alterning user/model strings.
 * @returns A Promise object that, if fulfilled, returns an object that represents the model's response.
 */
export const performTurn = async convo => {
  const messages = convo.map((content, i) => ({author: `${i % 2}`, content}))

  return getGuess({
    context: QUICKPROMPT_PROMPT_COMPONENTS.context,
    examples: QUICKPROMPT_PROMPT_COMPONENTS.examples,
    messages: messages
  })
}
