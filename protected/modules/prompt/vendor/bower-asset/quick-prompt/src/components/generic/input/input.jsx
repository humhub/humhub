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

import {useCallback, useEffect, useRef, useState} from 'react'
import PropTypes from 'prop-types'
import c from 'classnames'
import {useDidWin} from '../../../store'
import styles from './input.module.scss'

/**
 * This is a fix for the mobile when the input is focused, there
 * is extra white space at the bottom of the screen, so we prevent
 * them from scrolling.
 */
const handleScroll = e => {
  e.preventDefault()
}

const Input = ({
  onSubmit,
  onInputChange,
  inputVal,
  disabled,
  onFocus,
  forceFocus
}) => {
  const ref = useRef(null)
  const [focused, setFocused] = useState(false)
  const didWin = useDidWin()

  useEffect(() => {
    const scrollEvent = 'touchmove'
    if (focused) {
      document.addEventListener(scrollEvent, handleScroll, {passive: false})
    } else {
      document.removeEventListener(scrollEvent, handleScroll, {passive: false})
    }
    onFocus(focused)
  }, [focused, onFocus])

  // add a keydown handler to the input when the user presses enter
  const onKeyDown = useCallback(
    event => {
      if (didWin || disabled) return

      if (event.key === 'Enter') {
        //  ref.current.blur()
        onSubmit()
        ref.current.focus()
        onFocus(true)
      }
    },
    [onSubmit, onFocus, didWin, disabled]
  )

  useEffect(() => {
    if (forceFocus && ref.current) {
      ref.current.focus()
    }
  }, [forceFocus])

  return (
    <div
      className={c(
        styles.input,
        focused && styles.focused,
        !focused && styles.noFocus,
        disabled && styles.disabled
      )}
    >
      <input
        ref={ref}
        type="text"
        autoComplete="off"
        placeholder="Describe it…"
        value={inputVal}
        onFocus={() => setFocused(true)}
        onBlur={() => setFocused(false)}
        onChange={onInputChange}
        onKeyDown={onKeyDown}
      />
      <button
        className={styles.submit}
        onMouseDown={event => {
          event.preventDefault()
          onSubmit()
        }}
      >
        <span
          className="material-symbols-outlined"
          style={{pointerEvents: 'none'}}
        >
          send
        </span>
      </button>
    </div>
  )
}

Input.propTypes = {
  onSubmit: PropTypes.func.isRequired,
  onInputChange: PropTypes.func.isRequired,
  inputVal: PropTypes.string.isRequired,
  disabled: PropTypes.bool,
  onFocus: PropTypes.func,
  forceFocus: PropTypes.bool
}

export default Input
