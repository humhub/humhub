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

import {useEffect, useRef, useState} from 'react'
import PropTypes from 'prop-types'
import {useSpring, animated} from '@react-spring/web'
import {useDrag} from '@use-gesture/react'
import c from 'classnames'
import styles from './bottomSheet.module.scss'

const BottomSheet = ({headline, children, onEvent, open}) => {
  const [active, setActive] = useState(false)
  const maxY = useRef(0)
  const ref = useRef(null)
  const [{y}, api] = useSpring(() => ({y: 0}))

  const pressRelease = movement => {
    const newY = maxY.current + movement
    if (newY > getMaxY() * 0.66) {
      animateOut()
    } else animateIn(null)
  }

  const animateIn = movement => {
    if (!ref.current) return
    const maxHeight = getMaxY()

    if (movement === null) {
      setActive(true)
      api.start({y: maxHeight})
      if (onEvent) onEvent(true)
      return
    }

    let newY = maxY.current + movement
    if (newY < maxY.current) {
      newY = maxY.current
    }

    api.start({
      y: Math.min(0, newY)
    })
  }

  const animateOut = () => {
    api.start({y: 0})
    setActive(false)
    if (onEvent) onEvent(false)
    if (ref.current) {
      ref.current.scrollTop = 0
    }
  }

  const bind = useDrag(
    state => {
      const {down, movement, pressed} = state
      if (down) {
        animateIn(movement[1])
      }
      if (!pressed) {
        pressRelease(movement[1])
      }
    },
    {
      preventDefault: true,
      preventScroll: 0,
      preventScrollAxis: {y: 0},
      filterTaps: true,
      delay: 0,
      threshold: 0,
      swipe: {
        distance: 10
      }
    }
  )

  const getMaxY = () => {
    if (ref.current) return ref.current.getBoundingClientRect().height * -1 || 0
    return 0
  }

  useEffect(() => {
    if (ref.current) {
      ref.current.style.height = `${window.innerHeight * 0.825}px`
    }
  }, [ref])

  useEffect(() => {
    if (!active && open) {
      animateIn(null)
    }
    if (active && !open) {
      animateOut()
    }
    // ensure effect only triggers when props the bottom sheet opens
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open])

  useEffect(() => {
    maxY.current = getMaxY()
  }, [])

  return (
    <>
      <button
        style={{height: 'var(--viewport-height)'}}
        className={c(styles.darkSkim, {[styles.open]: open})}
        aria-label="close bottom sheet"
        onClick={animateOut}
      />
      <div className={c(styles.bottomSheet, {[styles.active]: active})}>
        <animated.div style={{y}}>
          <div ref={ref} className={`${styles.modal}`}>
            {headline && <h3>{headline}</h3>}
            {children}
            <div {...bind()} className={styles.dragHandle}>
              <div />
            </div>
          </div>
        </animated.div>
      </div>
    </>
  )
}

BottomSheet.propTypes = {
  headline: PropTypes.string,
  children: PropTypes.node,
  onEvent: PropTypes.func,
  open: PropTypes.bool
}

export default BottomSheet
