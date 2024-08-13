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

import {useEffect, useState, useRef} from 'react'
import PropTypes from 'prop-types'
import c from 'classnames'
import Toggle from '../../generic/toggle/toggle'
import {copyTranscript} from '../transcript/transcript'
import {
  useMuted,
  useActions,
  useHelp,
  useComputerVoice,
  useDialogue
} from '../../../store'
import {initSpeechSynth, checkVoiceSupport} from '../../../lib/speech'
import styles from './settings.module.scss'

const Settings = ({className}) => {
  const muted = useMuted()
  const computerVoice = useComputerVoice()
  const help = useHelp()
  const moreButtonRef = useRef()
  const {setMute, setHelp, setComputerVoice, stopGame} = useActions()
  const [showPopup, setShowPopup] = useState(false)
  const dialogue = useDialogue()
  const [voiceSupport, setVoiceSupport] = useState(false)

  useEffect(() => {
    if (moreButtonRef.current) moreButtonRef.current.focus()
    if (showPopup) setVoiceSupport(checkVoiceSupport())
  }, [showPopup])

  return (
    <div className={c(styles.settings, className)}>
      <button
        aria-label={muted ? 'unmute' : 'mute'}
        onClick={() => setMute(!muted)}
      >
        {muted ? (
          <span className="material-symbols-outlined">volume_off</span>
        ) : (
          <span className="material-symbols-outlined">&#xE050;</span>
        )}
      </button>
      <button aria-label="about & licenses" onClick={() => setHelp(!help)}>
        <span className="material-symbols-outlined">help</span>
      </button>
      <button
        ref={moreButtonRef}
        aria-label="show more settings"
        onClick={() => setShowPopup(true)}
      >
        <span className="material-symbols-outlined">more_vert</span>
      </button>
      {showPopup && (
        <div
          className={styles.touchArea}
          role="none"
          onClick={() => setShowPopup(false)}
        />
      )}

      <div
        aria-hidden={showPopup ? 'false' : 'true'}
        className={c(styles.popup, showPopup && styles.show)}
      >
        <button
          tabIndex={showPopup ? 0 : -1}
          aria-label={muted ? 'unmute' : 'mute'}
          className={c(styles.sound)}
          onClick={() => setMute(!muted)}
        >
          <span className="material-symbols-outlined">&#xE050;</span> Sound{' '}
          <Toggle active={!muted} />
        </button>
        {voiceSupport && (
          <button
            tabIndex={showPopup ? 0 : -1}
            aria-label="use computer voice"
            onClick={() => {
              initSpeechSynth()
              setComputerVoice(!computerVoice)
            }}
          >
            <span className="material-symbols-outlined">record_voice_over</span>{' '}
            Computer Voice <Toggle active={computerVoice} />
          </button>
        )}
        <button
          tabIndex={showPopup ? 0 : -1}
          aria-label="copy transcript"
          aria-disabled={dialogue.length === 0}
          className={c(dialogue.length === 0 && styles.disabled)}
          onClick={() => copyTranscript(dialogue)}
        >
          <span className="material-symbols-outlined">copy_all</span> Copy
          Transcript
        </button>
        <button
          tabIndex={showPopup ? 0 : -1}
          aria-label="about & licenses"
          className={c(styles.about)}
          onClick={() => {
            setHelp(!help)
            setShowPopup(false)
          }}
        >
          <span className="material-symbols-outlined">info</span> About &
          Licenses
        </button>
        <button
          tabIndex={showPopup ? 0 : -1}
          aria-label="end game"
          onClick={() => {
            setShowPopup(false)
            stopGame()
          }}
        >
          <span className="material-symbols-outlined">logout</span> End Game
        </button>
      </div>
    </div>
  )
}

Settings.propTypes = {
  className: PropTypes.string
}

export default Settings
