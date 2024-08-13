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
import PropTypes from 'prop-types'
import c from 'classnames'
import {useBadInput} from '../../../store'
import styles from './forbiddenPopup.module.scss'

const ForbiddenPopup = ({className}) => {
  const badInput = useBadInput()
  const [positions, setPositions] = useState([])
  const [doShow, setDoShow] = useState(false)
  const [doHide, setDoHide] = useState(false)
  const [forbiddenWords, setForbiddenWords] = useState([])

  useEffect(() => {
    if (forbiddenWords.length === 0) return

    window.setTimeout(() => {
      const positions = forbiddenWords.map(word => {
        const {x, y} = word.getBoundingClientRect()
        return [x, y]
      })

      setPositions(positions)
    }, 500)
  }, [forbiddenWords])

  useEffect(() => {
    if (positions.length === 0) return

    setDoShow(true)

    window.setTimeout(() => {
      setDoHide(true)
    }, 1500)

    return () => {
      setDoShow(false)
      setDoHide(false)
    }
  }, [positions])

  useEffect(() => {
    setForbiddenWords(
      [].slice.call(
        document.body.querySelectorAll('.dialogueList li:last-child .forbidden')
      )
    )
  }, [])

  return (
    <div
      className={c(
        styles.forbiddenPopup,
        className,
        doShow && styles.show,
        doHide && styles.hide
      )}
    >
      {forbiddenWords.map((word, i) => (
        <mark
          key={`${word}-${i}`}
          className={'forbidden'}
          style={{
            transform: positions[i]
              ? `translate(${positions[i][0]}px, ${positions[i][1]}px)`
              : 'none'
          }}
        >
          {badInput[i]}
          <div className={c(styles.whoops)}>🫢</div>
        </mark>
      ))}
    </div>
  )
}

ForbiddenPopup.propTypes = {
  className: PropTypes.string
}

export default ForbiddenPopup
