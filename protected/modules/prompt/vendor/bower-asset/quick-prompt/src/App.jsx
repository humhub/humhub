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

import {useEffect, useState} from 'react'
import c from 'classnames'
import GameOverScreen from './components/layouts/gameOverScreen/gameOverScreen'
import StartScreen from './components/layouts/startScreen/startScreen'
import Header from './components/generic/header/header'
import GameScreen from './components/layouts/gameScreen/gameScreen'
import AboutScreen from './components/layouts/aboutScreen/aboutScreen'
import SuccessPopup from './components/ui/successPopup/successPopup'
import ForbiddenPopup from './components/ui/forbiddenPopup/forbiddenPopup'
import BottomSheet from './components/ui/bottomSheet/bottomSheet'
import {
  useGamePrompt,
  useDidWin,
  useActions,
  useGameOver,
  useGameStarted,
  useHelp,
  useInputActive,
  useBadInput,
  useIsAndroid
} from './store'
import {useIsMobile} from './lib/utils'

const App = () => {
  const [headerHeight, setHeaderHeight] = useState(null)
  const inputActive = useInputActive()
  const gamePrompt = useGamePrompt()
  const help = useHelp()
  const gameOver = useGameOver()
  const gameStarted = useGameStarted()
  const didWin = useDidWin()
  const badInput = useBadInput()
  const {fetchWords, startRound, setHelp} = useActions()
  const isMobile = useIsMobile()
  const isAndroid = useIsAndroid()

  const setHeight = () => {
    document.documentElement.style.setProperty(
      '--viewport-height',
      `${window.visualViewport.height}px`
    )

    document.documentElement.style.setProperty(
      '--padding-top',
      `${Math.round(window.innerHeight - window.visualViewport.height)}px`
    )
  }

  useEffect(() => {
    if (isAndroid) {
      document.documentElement.classList.add('android')
    }
  }, [isAndroid])

  useEffect(() => {
    fetchWords()
    setHeight()
    window.addEventListener('resize', setHeight)

    return () => {
      window.removeEventListener('resize', setHeight)
    }
  }, [fetchWords])

  /**
   * This effect is used to set the height of the main element
   * If the viewport width is smaller than the mobile breakpoint and the input is active,
   * Subtract the margin from the header height because the logo and the settings button
   * are hidden when the input is active.
   */
  useEffect(() => {
    const header = document.querySelector('header')
    const margin = isMobile && inputActive ? 50 : 0

    setHeaderHeight(header.offsetHeight - margin)
    setHeight()

    // This is a fix for Safari on iOS
    // use a setTimeout to resize elements after the keyboard opens
    if (isMobile) {
      window.setTimeout(() => {
        setHeaderHeight(header.offsetHeight - margin)
        setHeight()
        window.scrollTo(0, 0)
      }, 750)
    }
  }, [isMobile, gamePrompt.word, gameStarted, gameOver, inputActive])

  return (
    <>
      <Header />
      {isMobile && (
        <BottomSheet open={help} onEvent={event => setHelp(event)}>
          <AboutScreen />
        </BottomSheet>
      )}
      <main
        style={{
          height: `calc(var(--viewport-height) - ${headerHeight}px)`,
          transition: 'height 0.3s ease-in-out'
        }}
        className={c(gamePrompt && gamePrompt.word && 'active')}
      >
        {!isMobile && <AboutScreen />}
        {!gameStarted && <StartScreen onStart={startRound} />}
        {gameStarted && !gameOver && <GameScreen />}
        {gameOver && <GameOverScreen />}
      </main>
      {didWin && <SuccessPopup />}
      {badInput && <ForbiddenPopup />}
    </>
  )
}

export default App
