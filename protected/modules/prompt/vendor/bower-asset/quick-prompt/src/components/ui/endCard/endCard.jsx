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
import {useGuessedWords, useScore} from '../../../store'
import styles from './endCard.module.scss'

const affirmations = [
  'Good try!', // [0 - 5 words]
  'Nice job!', // [6 - 10 words]
  'Great work!', // [11 - 15 words]
  'Amazing!', // [16-20]
  'Inhuman!' // [40+]
]

const getAffirmation = count => {
  let i = 0
  if (count > 5) i = 1
  if (count > 10) i = 2
  if (count > 15) i = 3
  if (count > 20) i = 4

  return affirmations[i]
}

const EndCard = () => {
  const guessedWords = useGuessedWords()
  const score = useScore()

  return (
    <div className={c(styles.endCard)}>
      <div className={c(styles.content)}>
        <h2>
          <b>{getAffirmation(score)}</b> You got your <br /> AI teammate to
          guess
        </h2>
        <h1>
          {score} word{score !== 1 && 's'}
        </h1>
        <h2>
          {guessedWords
            .map(({emoji, word}) => {
              return `${emoji || '🙃'} ${word}`
            })
            .join(', ')}
        </h2>
      </div>
      <div className={styles.sheet}>
        Made with the Google Generative Language API
      </div>
    </div>
  )
}

export default EndCard
