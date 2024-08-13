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
import BorderButton from '../../generic/borderButton/borderButton'
import EndCard from '../../ui/endCard/endCard'
import Transcript from '../../ui/transcript/transcript'
import {useActions, useGuessedWords, useScore} from '../../../store'
import styles from './gameOverScreen.module.scss'

const GameOverScreen = () => {
  const {startRound} = useActions()
  const score = useScore()
  const guessedWords = useGuessedWords()

  const copyDialogue = async () => {
    const text = `My Quick, Prompt! score: I got the AI to guess ${score} words:\n${guessedWords
      .map(word => `${word.emoji} ${word.word}`)
      .join('\n')}`
    try {
      await navigator.clipboard.writeText(text)
    } catch (err) {
      console.error('Failed to copy: ', err)
    }
  }

  return (
    <div className={c(styles.gameOverScreen)}>
      <div className="container inset">
        <div className={styles.spacer}>
          <EndCard count={score} />
        </div>
        <div className={styles.spacer}>
          <BorderButton onClick={copyDialogue}>
            <span className="emoji">📋</span>
            &nbsp;{'Copy shareable text'}
          </BorderButton>
        </div>
        <div className={styles.spacerLarge}>
          <BorderButton text="😏 Play again" onClick={() => startRound(true)}>
            <span className="emoji">😏</span>
            &nbsp;{'Play again'}
          </BorderButton>
        </div>
        <div className={styles.spacerLarge}>
          <Transcript />
        </div>
      </div>
    </div>
  )
}

export default GameOverScreen
