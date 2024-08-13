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

import {useRef, useEffect} from 'react'
import c from 'classnames'
import StatusBar from '../statusBar/statusBar'
import Settings from '../../ui/settings/settings'
import {
  useActions,
  useGameStarted,
  useHelp,
  useInputActive
} from '../../../store'
import {useIsMobile} from '../../../lib/utils'
import styles from './header.module.scss'

const Header = () => {
  const gameStarted = useGameStarted()
  const help = useHelp()
  const {setHelp, stopGame} = useActions()
  const isMobile = useIsMobile()
  const inputActive = useInputActive()
  const closeRef = useRef()

  const stop = () => {
    if (gameStarted) {
      stopGame({showStart: true})
    }
  }

  /**
   * This effect is used to focus the close button when the help screen is opened
   */
  useEffect(() => {
    if (closeRef.current && help) closeRef.current.focus()
  }, [help])

  return (
    <header
      className={c(
        styles.header,
        gameStarted && 'extended',
        inputActive && styles.inputActive
      )}
    >
      <button className={styles.logo} onClick={stop}>
        Quick, Prompt!
      </button>
      {
        <>
          <Settings className={c(styles.settings, help && styles.hide)} />
          {gameStarted && (
            <StatusBar className={c(styles.statusBar, help && styles.hide)} />
          )}
          {!isMobile && (
            <button
              ref={closeRef}
              tabIndex={help ? 0 : -1}
              aria-hidden={help ? 'false' : 'true'}
              className={c(styles.closeBtn, help && styles.show)}
              onClick={() => setHelp(false)}
            >
              <span className="material-symbols-outlined">close</span>
            </button>
          )}
        </>
      }
    </header>
  )
}

export default Header
