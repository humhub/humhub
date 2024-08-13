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
import EmojiBackground from '../../generic/emojiBackground/emojiBackground'
import {useActions, useWords} from '../../../store'
import styles from './startScreen.module.scss'

const StartScreen = () => {
  const {startRound} = useActions()
  const words = useWords()

  return (
    <>
      <EmojiBackground />
      <div className={c(styles.startScreen, 'container')}>
        <div>
          <h2>
            <span className="line">Get your AI teammate</span>{' '}
            <span className="line">to guess the right word!</span>
          </h2>
          <h3>
            <span className="line">Give it any clue you want, just</span>{' '}
            <span className="line">
              don’t say the forbidden <br />
              words.
            </span>
          </h3>
        </div>
        <BorderButton
          className={c(styles.playButton)}
          disabled={words.length === 0}
          onClick={() => {
            startRound(true)
          }}
        >
          {'Let’s play!'}
        </BorderButton>
      </div>
    </>
  )
}

export default StartScreen
