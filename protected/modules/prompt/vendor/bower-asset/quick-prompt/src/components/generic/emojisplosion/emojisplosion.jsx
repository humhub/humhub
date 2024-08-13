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

import {useEffect, useState, memo, useMemo} from 'react'
import {v4 as uuid} from 'uuid'
import c from 'classnames'
import {useGamePrompt} from '../../../store'
import styles from './emojisplosion.module.scss'

const Emojisplosion = memo(function Emojisplosion() {
  const {emoji} = useGamePrompt()
  const [doPlay, setDoPlay] = useState(false)

  const emojis = useMemo(
    () => [...emoji].filter(event => !event.match(/\s|\t|\n/)).concat('⭐️'),
    [emoji]
  )

  useEffect(() => {
    setDoPlay(false)
    window.setTimeout(() => {
      setDoPlay(true)
    }, 750)
  }, [])

  return (
    <div className={c(styles.emojisplosion, doPlay && styles.play)}>
      {Array.from(Array(15), () => (
        <div key={uuid()}>
          {emojis[Math.floor(Math.random() * emojis.length)]}
        </div>
      ))}
    </div>
  )
})

export default Emojisplosion
