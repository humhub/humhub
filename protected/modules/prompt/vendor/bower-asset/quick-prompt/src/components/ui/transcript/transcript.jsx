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

import c from 'classnames'
import {v4 as uuid} from 'uuid'
import BorderButton from '../../generic/borderButton/borderButton'
import {useDialogue} from '../../../store'
import styles from './transcript.module.scss'

export const copyTranscript = async dialogue => {
  let text =
    '📄 This is a transcript from the game Quick, Prompt\n👉 quickprompt.withgoogle.com\n🛠 Made with the Google Generative Language API\n\n'
  text += dialogue
    .map(
      item =>
        `${item.isModel ? 'AI' : 'You'}: ${item.text.replace(
          /(\[|\]|<mark class="forbidden">|<\/mark>)/g,
          ''
        )}`
    )
    .join('\n')
  try {
    await navigator.clipboard.writeText(text)
  } catch (err) {
    console.error('Failed to copy: ', err)
  }
}

const Transcript = () => {
  const dialogue = useDialogue()

  return (
    <div className={c(styles.transcriptRoot)}>
      <h2>Game transcript</h2>
      <div className={styles.transcript}>
        <ul>
          {dialogue &&
            dialogue.map(item => (
              <li key={uuid()}>
                <b>{item.isModel ? 'AI' : 'You'}:</b>{' '}
                {item.text.replace(
                  /(\[|\]|<mark class="forbidden">|<\/mark>)/g,
                  ''
                )}
              </li>
            ))}
        </ul>
      </div>
      <BorderButton onClick={() => copyTranscript(dialogue)}>
        <span className="emoji">📋</span>
        &nbsp;{'Copy transcript'}
      </BorderButton>
    </div>
  )
}

export default Transcript
