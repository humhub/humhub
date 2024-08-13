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

const audioContext = new AudioContext()
const audioBuffers = {}

/**
 * Get an AudioBuffer from cache, or retrieve the audio asset from the provided URL if the AudioBuffer doesn't exist in cache.
 * 
 * @param {string} url The URL from which to retrieve the audio asset.
 * @returns {Promise<AudioBuffer>}
 */
const getAudio = async url => {
  if (!audioBuffers[url]) {
    audioBuffers[url] = await fetchAudio(url)
  }
  return audioBuffers[url]
}

/**
 * Retrieve an audio asset from a URL.
 * 
 * @param {string} url The URL from which to retrieve the audio asset.
 * @returns {Promise<AudioBuffer>}
 */
const fetchAudio = async url => {
  const response = await fetch(url)
  const arrayBuffer = await response.arrayBuffer()
  const audioBuffer = await audioContext.decodeAudioData(arrayBuffer)
  return audioBuffer
}

/**
 * Play an audio asset retrieved from a URL.
 * 
 * @param {string} url The URL from which to retrieve the audio asset.
 * @returns {Promise}
 */
export const playSound = async url => {
  const audioBuffer = await getAudio(url)
  const source = audioContext.createBufferSource()
  source.buffer = audioBuffer
  source.connect(audioContext.destination)
  source.start()
}
