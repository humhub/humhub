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

export const GAME_START_REQUEST = 'Shall we begin the game?'
export const GAME_START_RESPONSE = 'Ready whenever you are!'
export const SKIP_REQUEST = "Let's skip this word."
export const SKIP_RESPONSE = 'On to the next!'
export const FORBIDDEN_WORD_RESPONSE =
  "Oops, looks like that hint wasn't allowed. Let's move on to the next word."
export const NEW_WORD_PREFIX = "Yes! Ok, next word. Here's a hint:"
export const USER_TURN_SUFFIX = '(Please enclose your guess in square brackets)'

export const QUICKPROMPT_PROMPT_COMPONENTS = {
  context:
    "We are going to play a game where you try and guess the word I'm thinking of. I'll start by giving you an initial hint, after which you should make your first guess. If you don't get it right, I'll provide an additional hint, and then you'll guess again. This process will continue until you guess correctly, or we run out of time. Make sure to enclose the word you're guessing within [square brackets]. And please guess only one word at a time, no lists allowed.",
  examples: [
    {
      input: {
        author: '0',
        content: `This is a thing into which feathers go, and it makes it more comfortable to sit. ${USER_TURN_SUFFIX}`
      },
      output: {
        author: '1',
        content: 'Hmm, ... a [pillow]?'
      }
    },
    {
      input: {
        author: '0',
        content: `You might find this on a couch. ${USER_TURN_SUFFIX}`
      },
      output: {
        author: '1',
        content: "Ah, okay, I've got it! 😃 It's a [cushion]!"
      }
    },
    {
      input: {
        author: '0',
        content: `${NEW_WORD_PREFIX} This is something you might use when drops are falling from the sky. ${USER_TURN_SUFFIX}`
      },
      output: {
        author: '1',
        content: 'Are you thinking of an [umbrella]?'
      }
    },
    {
      input: {
        author: '0',
        content: `${NEW_WORD_PREFIX} Oops ${USER_TURN_SUFFIX}`
      },
      output: {
        author: '1',
        content: 'Hmm, ... is it a [mistake]?'
      }
    },
    {
      input: {
        author: '0',
        content: `${NEW_WORD_PREFIX} Boo-boo ${USER_TURN_SUFFIX}`
      },
      output: {
        author: '1',
        content: 'Hmmm, 🤔 this is hard ... a [cut]?'
      }
    },
    {
      input: {
        author: '0',
        content: `Another word for that. ${USER_TURN_SUFFIX}`
      },
      output: {
        author: '1',
        content: 'Oh! Is it a [wound]?'
      }
    },
    {
      input: {
        author: '0',
        content: `${NEW_WORD_PREFIX} It's when creatures move somewhere else during colder part of the year. ${USER_TURN_SUFFIX}`
      },
      output: {
        author: '1',
        content: "I think it's [migrate]?"
      }
    },
    {
      input: {
        author: '0',
        content: `Noun form of that word. ${USER_TURN_SUFFIX}`
      },
      output: {
        author: '1',
        content: 'Oh, got it! It must be [migration]! 🙌'
      }
    },
    {
      input: {
        author: '0',
        content: `${NEW_WORD_PREFIX} It's on food items and tells you the price. ${USER_TURN_SUFFIX}`
      },
      output: {
        author: '1',
        content: 'Hmm ... 🤔 is it a [price tag]?'
      }
    },
    {
      input: {
        author: '0',
        content: `Think more general. There's adhesive on the back so it stays. ${USER_TURN_SUFFIX}`
      },
      output: {
        author: '1',
        content: 'Ah, I see. A [sticker]!'
      }
    }
  ]
}
