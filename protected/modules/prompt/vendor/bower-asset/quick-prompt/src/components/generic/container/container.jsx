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

import PropTypes from 'prop-types'
import c from 'classnames'
import styles from './container.module.scss'

const Container = ({children, inset, className}) => {
  return (
    <section className={c(styles.container, inset && styles.inset, className)}>
      <div className={c(styles.content)}>{children}</div>
    </section>
  )
}

Container.propTypes = {
  children: PropTypes.node,
  inset: PropTypes.bool,
  className: PropTypes.string
}

export default Container
