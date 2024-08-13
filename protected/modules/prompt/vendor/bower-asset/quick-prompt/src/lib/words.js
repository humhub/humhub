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

const allWords = {
  easy_words: [
    {
      emoji: '🦁',
      word: 'lion',
      disallowed: ['mane', 'roar', 'cat', 'savanna', 'king']
    },
    {
      emoji: '🐯',
      word: 'tiger',
      disallowed: ['predator', 'cat', 'stripes', 'orange', 'jungle']
    },
    {
      emoji: '🐻',
      word: 'bear',
      disallowed: ['mammal', 'brown', 'hibernation', 'sleep', 'fur']
    },
    {
      emoji: '🐱',
      word: 'cat',
      disallowed: ['feline', 'meow', 'purr', 'hairball', 'kitten']
    },
    {
      emoji: '🐶',
      word: 'dog',
      disallowed: ['pet', 'best friend', 'loyal', 'companion', 'animal']
    },
    {
      emoji: '🐦',
      word: 'bird',
      disallowed: ['beak', 'feather', 'fly', 'tweet', 'tree']
    },
    {
      emoji: '🦇',
      word: 'bat',
      disallowed: ['nocturnal', 'sonar', 'wings', 'superhero', 'fly']
    },
    {
      emoji: '🐑',
      word: 'sheep',
      disallowed: ['herd', 'wool', 'hair', 'lamb', 'farm']
    },
    {
      emoji: '🐒',
      word: 'monkey',
      disallowed: ['jungle', 'ape', 'banana', 'chimp', 'hairy']
    },
    {
      emoji: '🐘',
      word: 'elephant',
      disallowed: ['big', 'grey', 'tusk', 'animal', 'trunk']
    },
    {
      emoji: '🐋',
      word: 'whale',
      disallowed: ['ocean', 'blue', 'mammal', 'big', 'aquatic']
    },
    {
      emoji: '🍷',
      word: 'wine glass',
      disallowed: ['stem', 'drink', 'alcohol', 'beverage', 'grapes']
    },
    {
      emoji: '🍾🔩',
      word: 'corkscrew',
      disallowed: ['wine', 'bottle', 'turn', 'open', 'spiral']
    },
    {
      emoji: '🔪',
      word: 'knife',
      disallowed: ['cut', 'sharp', 'blade', 'cook', 'chop']
    },
    {
      emoji: '🥄',
      word: 'spoon',
      disallowed: ['soup', 'liquid', 'utensil', 'stir', 'hold']
    },
    {
      emoji: '👨‍🍳🥖',
      word: 'oven',
      disallowed: ['bake', 'broil', 'cookie', 'cook. roast']
    },
    {
      emoji: '🍞🔥',
      word: 'toaster',
      disallowed: ['bread', 'heat', 'bagel', 'burn', 'crisp']
    },
    {
      emoji: '🥩',
      word: 'steak',
      disallowed: ['beef', 'meat', 'cut', 'filet', 'cow']
    },
    {
      emoji: '🍫',
      word: 'brownie',
      disallowed: ['batter', 'chocolate', 'bake', 'pastry', 'fudge']
    },
    {
      emoji: '🍦',
      word: 'ice cream',
      disallowed: ['milk', 'sweet', 'dessert', 'frozen', 'freezer']
    },
    {
      emoji: '☕',
      word: 'coffee',
      disallowed: ['bean', 'water', 'caffeine', 'latte', 'drink']
    },
    {
      emoji: '🍔',
      word: 'hamburger',
      disallowed: ['bun', 'beef', 'patty', 'cheese', 'sandwich']
    },
    {
      emoji: '🍕',
      word: 'pizza',
      disallowed: ['dough', 'crust', 'italian', 'cheese', 'tomato']
    },
    {
      emoji: '🌭',
      word: 'hot dog',
      disallowed: ['bun', 'sausage', 'mustard', 'ketchup', 'grill']
    },
    {
      emoji: '🥪',
      word: 'sandwich',
      disallowed: ['bread', 'meat', 'cheese', 'lunch', 'between']
    },
    {
      emoji: '🍪',
      word: 'cookie',
      disallowed: ['dough', 'sweet', 'dessert', 'chocolate', 'bake']
    },
    {
      emoji: '🍰',
      word: 'cake',
      disallowed: ['batter', 'birthday', 'dessert', 'bake', 'frosting']
    },
    {
      emoji: '🍭',
      word: 'lollipop',
      disallowed: ['candy', 'sugar', 'stick', 'hard', 'round']
    },
    {
      emoji: '👞',
      word: 'shoes',
      disallowed: ['feet', 'socks', 'laces', 'soles', 'accessory']
    },
    {
      emoji: '🧦',
      word: 'socks',
      disallowed: ['feet', 'shoes', 'soft', 'toes', 'cloth']
    },
    {
      emoji: '🧥',
      word: 'jacket',
      disallowed: ['outerwear', 'coat', 'warm', 'winter', 'parka']
    },
    {
      emoji: '👀',
      word: 'eyes',
      disallowed: ['sight', 'vision', 'face', 'pupil', 'glasses']
    },
    {
      emoji: '👂',
      word: 'ears',
      disallowed: ['hear', 'sound', 'noise', 'face', 'rings']
    },
    {
      emoji: '👄',
      word: 'mouth',
      disallowed: ['lips', 'teeth', 'gums', 'eat', 'chew']
    },
    {
      emoji: '👃',
      word: 'nose',
      disallowed: ['smell', 'scent', 'nostrils', 'breathing', 'face']
    },
    {
      emoji: '🚿',
      word: 'shower',
      disallowed: ['bath', 'clean', 'body', 'wash', 'drain']
    },
    {
      emoji: '🧼',
      word: 'soap',
      disallowed: ['wash', 'clean', 'shower', 'bath', 'scrub']
    },
    {
      emoji: '🧴💆',
      word: 'shampoo',
      disallowed: ['hair', 'wash', 'conditioner', 'locks', 'shower']
    },
    {
      emoji: '🛏️',
      word: 'bed',
      disallowed: ['sleep', 'sheets', 'rest', 'pillows', 'mattress']
    },
    {
      emoji: '⏰',
      word: 'clock',
      disallowed: ['time', 'hands', 'alarm', 'watch', 'hour']
    },
    {
      emoji: '🚪👗',
      word: 'closet',
      disallowed: ['storage', 'clothes', 'hangers', 'drawers', 'home']
    },
    {
      emoji: '💪',
      word: 'arm',
      disallowed: ['hand', 'elbow', 'body', 'shoulder', 'wrist']
    },
    {
      emoji: '🤚',
      word: 'hand',
      disallowed: ['finger', 'palm', 'point', 'wave', 'clap']
    },
    {
      emoji: '👣',
      word: 'foot',
      disallowed: ['toe', 'shoe', 'ankle', 'sock', 'body']
    },
    {
      emoji: '🚰',
      word: 'faucet',
      disallowed: ['water', 'drip', 'drain', 'sink', 'bathtub']
    },
    {
      emoji: '🛁',
      word: 'bathtub',
      disallowed: ['shower', 'clean', 'wash', 'water', 'soap']
    },
    {
      emoji: '🚽',
      word: 'toilet',
      disallowed: ['potty', 'bathroom', 'flush', 'porcelain', 'seat']
    },
    {
      emoji: '🪥',
      word: 'toothbrush',
      disallowed: ['mouth', 'tooth', 'clean', 'paste', 'bristle']
    },
    {
      emoji: '🦏',
      word: 'rhinoceros ',
      disallowed: ['animal', 'horn', 'gray', 'africa']
    },
    {
      emoji: '🦝',
      word: 'racoon',
      disallowed: ['animal', 'nocturnal', 'trash', 'mask', 'fur']
    },
    {
      emoji: '🐿️',
      word: 'squirrel',
      disallowed: ['animal', 'tail', 'tree', 'climb', 'nut']
    },
    {
      emoji: '🦩',
      word: 'flamingo',
      disallowed: ['bird', 'pink', 'water', 'legs', 'beak']
    },
    {
      emoji: '🍓',
      word: 'strawberry',
      disallowed: ['fruit', 'red', 'green', 'sweet', 'seed']
    },
    {
      emoji: '🍑',
      word: 'peach',
      disallowed: ['fuzz', 'pink', 'stone', 'yellow', 'fruit']
    },
    {
      emoji: '🍌',
      word: 'banana',
      disallowed: ['yellow', 'peel', 'fruit', 'monkey', 'bread']
    },
    {
      emoji: '🍎',
      word: 'apple',
      disallowed: ['fruit', 'red', 'peel', 'tree', 'core']
    },
    {
      emoji: '🍊',
      word: 'orange',
      disallowed: ['fruit', 'citrus', 'peel', 'juice', 'vitamin c']
    },
    {
      emoji: '🫐',
      word: 'blueberry',
      disallowed: ['fruit', 'muffin', 'pancake', 'antioxidant', 'small']
    },
    {
      emoji: '🍉',
      word: 'watermelon',
      disallowed: ['fruit', 'big', 'seed', 'pink', 'green']
    },
    {
      emoji: '🫖',
      word: 'teapot',
      disallowed: ['hot', 'water', 'brew', 'bag', 'boil']
    },
    {
      emoji: '🧺',
      word: 'basket',
      disallowed: ['handle', 'hold', 'weave', 'picnic', 'container']
    },
    {
      emoji: '🎛️',
      word: 'stove',
      disallowed: ['hot', 'burn', 'cook', 'kitchen', 'gas']
    },
    {
      emoji: '🥤',
      word: 'straw',
      disallowed: ['drink', 'liquid', 'hay', 'farm', 'yellow']
    },
    {
      emoji: '🥜',
      word: 'peanut',
      disallowed: ['shell', 'butter', 'legume', 'allergy', 'allergic']
    },
    {
      emoji: '🥔',
      word: 'potato',
      disallowed: ['vegetable', 'peel', 'fries', 'mash', 'ground']
    },
    {
      emoji: '🩳',
      word: 'shorts',
      disallowed: ['legs', 'clothes', 'clothing', 'summer', 'pants']
    },
    {
      emoji: '👕',
      word: 't-shirt',
      disallowed: ['clothes', 'clothing', 'top', 'arms', 'collar']
    },
    {
      emoji: '👖',
      word: 'jeans',
      disallowed: ['denim', 'pants', 'legs', 'blue', 'clothing']
    },
    {
      emoji: '🩲',
      word: 'underwear',
      disallowed: ['clothes', 'clothing', 'briefs', 'boxers', 'panties']
    },
    {
      emoji: '🩴',
      word: 'flip-flops',
      disallowed: ['foot', 'feet', 'shoe', 'sandal', 'thong']
    },
    {
      emoji: '💪',
      word: 'shoulder',
      disallowed: ['body', 'arm', 'joint', 'neck', 'part']
    },
    {
      emoji: '🤳',
      word: 'elbow',
      disallowed: ['body', 'part', 'joint', 'arm', 'bend']
    },
    {
      emoji: '👋',
      word: 'wrist',
      disallowed: ['body', 'part', 'joint', 'arm', 'hand']
    },
    {
      emoji: '✋',
      word: 'fingers',
      disallowed: ['body', 'part', 'joint', 'hand', 'digit']
    },
    {
      emoji: '🎽',
      word: 'chest',
      disallowed: ['body', 'part', 'torso', 'rib', 'shirt']
    },
    {
      emoji: '🤢',
      word: 'stomach',
      disallowed: ['organ', 'gut', 'food', 'digest', 'eat']
    },
    {
      emoji: '🦵',
      word: 'legs',
      disallowed: ['limb', 'stand', 'walk', 'knee', 'thigh']
    },
    {
      emoji: '🦵',
      word: 'knees',
      disallowed: ['joint', 'bend', 'leg', 'limb', 'body']
    },
    {
      emoji: '🥾',
      word: 'ankle',
      disallowed: ['joint', 'foot', 'leg', 'sprain', 'body']
    },
    {
      emoji: '👣',
      word: 'toe',
      disallowed: ['foot', 'digit', 'nail', 'sock', 'shoe']
    },
    {
      emoji: '🚰',
      word: 'drain',
      disallowed: ['sink', 'tub', 'water', 'pipe', 'clog']
    },
    {
      emoji: '🚽',
      word: 'toilet seat',
      disallowed: ['bathroom', 'sit', 'lid', 'restroom', 'cover']
    },
    {
      emoji: '🪥',
      word: 'toothpaste',
      disallowed: ['teeth', 'brush', 'clean', 'tube', 'gel']
    },
    {
      emoji: '🦷✨',
      word: 'floss',
      disallowed: ['teeth', 'string', 'clean', 'dentist', 'dental']
    },
    {
      emoji: '🚰',
      word: 'sink',
      disallowed: ['water', 'faucet', 'drain', 'bathroom', 'kitchen']
    },
    {
      emoji: '🛌',
      word: 'bedroom',
      disallowed: ['house', 'sleep', 'pillow', 'covers', 'closet']
    },
    {
      emoji: '🪶',
      word: 'pillow',
      disallowed: ['bed', 'sleep', 'soft', 'feather', 'rest']
    },
    {
      emoji: '🛏',
      word: 'sheets',
      disallowed: ['bed', 'sleep', 'fabric', 'cover', 'thread']
    },
    {
      emoji: '🛏',
      word: 'pillowcase',
      disallowed: ['cover', 'bed', 'fabric', 'feather', 'sleep']
    },
    {
      emoji: '🛌',
      word: 'blanket',
      disallowed: ['bed', 'cover', 'sheet', 'sleep', 'quilt']
    },
    {
      emoji: '🖼',
      word: 'poster',
      disallowed: ['wall', 'decoration', 'art', 'print', 'picture']
    },
    {
      emoji: '🗄',
      word: 'dresser',
      disallowed: ['furniture', 'drawer', 'clothes', 'bedroom', 'cabinet']
    },
    {
      emoji: '🗃',
      word: 'drawer',
      disallowed: ['furniture', 'slide', 'hold', 'handle', 'pull']
    },
    {
      emoji: '💨',
      word: 'fan',
      disallowed: ['blow', 'air', 'cool', 'wind', 'breeze']
    }
  ],
  medium_words: [
    {
      emoji: '🎶🗣',
      word: 'rap',
      disallowed: ['music', 'beat', 'beatboxing', 'song']
    },
    {
      emoji: '🤘⚡🎸',
      word: 'rock and roll',
      disallowed: ['band', 'elvis', 'music', 'guitar', 'genre']
    },
    {
      emoji: '📱📟',
      word: 'electronic',
      disallowed: ['phone', 'computer', 'power', 'charge', 'speakers']
    },
    {
      emoji: '🎩',
      word: 'hat',
      disallowed: ['top', 'cap', 'beanie', 'sombrero', 'head']
    },
    {
      emoji: '🔌💨',
      word: 'cooler',
      disallowed: ['cold', 'ice', 'freeze', 'camping', 'chill']
    },
    {
      emoji: '🕯️🔥',
      word: 'matches',
      disallowed: ['fire', 'light', 'candle', 'wick', 'spark']
    },
    {
      emoji: '🗺️',
      word: 'map',
      disallowed: ['atlas', 'globe', 'navigate', 'route', 'cartography']
    },
    {
      emoji: '🔥',
      word: 'campfire ',
      disallowed: ["s'mores", 'fire', 'warm', 'wood', 'smoke']
    },
    {
      emoji: '🏮',
      word: 'lantern',
      disallowed: ['light', 'dark', 'torch', 'candle', 'lamp']
    },
    {
      emoji: '🚌',
      word: 'bus',
      disallowed: ['transportation', 'route', 'public', 'station', 'stop']
    },
    {
      emoji: '🏠',
      word: 'house',
      disallowed: ['home', 'live', 'sleep', 'rooms', 'family']
    },
    {
      emoji: '🏡',
      word: 'yard',
      disallowed: ['outside', 'grass', 'garden', 'lawn', 'house']
    },
    {
      emoji: '🎶🎷',
      word: 'jazz',
      disallowed: ['blues', 'bluegrass', 'relaxing', 'rhythmic', 'instruments']
    },
    {
      emoji: '🎹🎼',
      word: 'classical',
      disallowed: ['mozart', 'beethoven', 'symphony', 'piano', 'strings']
    },
    {
      emoji: '🎤🎶',
      word: 'r&b',
      disallowed: ['music', 'blues', 'gospel', 'jazz', 'soul']
    },
    {
      emoji: '🍡',
      word: 'popsicle',
      disallowed: ['cold', 'cherry', 'ice cream', 'summer', 'stick']
    },
    {
      emoji: '👖🥋',
      word: 'belt',
      disallowed: ['pants', 'jeans', 'waist', 'buckle', 'tight']
    },
    {
      emoji: '🧣',
      word: 'scarf',
      disallowed: ['winter', 'neck', 'wrap', 'warm', 'knit']
    },
    {
      emoji: '👔',
      word: 'tie',
      disallowed: ['neck', 'collar', 'work', 'formal', 'clothing']
    },
    {
      emoji: '🔗👔',
      word: 'cufflinks',
      disallowed: ['wrist', 'shirt', 'gift', 'metal', 'clip']
    },
    {
      emoji: '⛺',
      word: 'tent',
      disallowed: ['camping', 'shelter', 'poles', 'fabric', 'sleep']
    },
    {
      emoji: '🛌👜',
      word: 'sleeping bag',
      disallowed: ['camping', 'nylon', 'portable', 'warm', 'ground']
    },
    {
      emoji: '🥃',
      word: 'tumbler',
      disallowed: ['glass', 'cup', 'drink', 'beverages', 'coffee']
    },
    {
      emoji: '🧭',
      word: 'compass',
      disallowed: ['directions', 'north', 'camping', 'pocket', 'sailors']
    },
    {
      emoji: '🪖',
      word: 'camouflage',
      disallowed: ['hide', 'military', 'blend', 'green', 'hunting']
    },
    {
      emoji: '👙',
      word: 'swimsuit',
      disallowed: ['tankini', 'pool', 'water', 'bikini', 'beach']
    },
    {
      emoji: '🏄🤿',
      word: 'wetsuit',
      disallowed: ['surf', 'water', 'beach', 'scuba diving', 'zipper']
    },
    {
      emoji: '🔨',
      word: 'hammer',
      disallowed: ['tool', 'hit', 'metal', 'construction', 'blunt']
    },
    {
      emoji: '💅',
      word: 'nail',
      disallowed: ['hammer', 'cuticle', 'finger', 'metal', 'hit']
    },
    {
      emoji: '🪚',
      word: 'saw',
      disallowed: ['tool', 'wood', 'metal', 'cut', 'see']
    },
    {
      emoji: '👂💍',
      word: 'earrings',
      disallowed: ['jewelry', 'pierced', 'lobes', 'ear', 'gold']
    },
    {
      emoji: '💍',
      word: 'ring',
      disallowed: ['finger', 'jewelry', 'engagement', 'wedding', 'diamond']
    },
    {
      emoji: '⌚️',
      word: 'watch',
      disallowed: ['time', 'wrist', 'clock', 'hour', 'band']
    },
    {
      emoji: '🚙',
      word: 'car',
      disallowed: ['drive', 'road', 'vehicle', 'seat', 'automobile']
    },
    {
      emoji: '🚕',
      word: 'taxi',
      disallowed: ['cab', 'ride', 'driver', 'transportation', 'automobile']
    },
    {
      emoji: '🚞',
      word: 'train',
      disallowed: ['tracks', 'transit', 'rail', 'locomotive', 'road']
    },
    {
      emoji: '🚦',
      word: 'stoplight',
      disallowed: ['traffic', 'light', 'go', 'stop', 'intersection']
    },
    {
      emoji: '📬',
      word: 'mailbox',
      disallowed: ['letter', 'mail', 'post office', 'send', 'deliver']
    },
    {
      emoji: '📺',
      word: 'tv',
      disallowed: ['shows', 'news', 'audio', 'visual', 'remote control']
    },
    {
      emoji: '🏢🏠',
      word: 'building',
      disallowed: ['house', 'office', 'city', 'structure', 'place']
    },
    {
      emoji: '🛑',
      word: 'stop sign',
      disallowed: ['break', 'drive', 'intersection', 'slow', 'wait']
    },
    {
      emoji: '💡',
      word: 'light bulb',
      disallowed: ['electricity', 'lumens', 'illuminate', 'brighten', 'lamp']
    },
    {
      emoji: '🪴',
      word: 'house plant',
      disallowed: ['pot', 'decoration', 'inside', 'grow', 'seeds']
    },
    {
      emoji: '🧹',
      word: 'broom',
      disallowed: ['clean', 'dust', 'house', 'sweep', 'floor']
    },
    {
      emoji: '🪣',
      word: 'bucket',
      disallowed: ['container', 'water', 'handle', 'liquids', 'cylindrical']
    },
    {
      emoji: '🏀',
      word: 'basketball',
      disallowed: ['bounce', 'orange', 'game', 'hoop', 'shoot']
    },
    {
      emoji: '⚾️',
      word: 'baseball',
      disallowed: ['bat', 'innings', 'strike', 'outfield', 'pitch']
    },
    {
      emoji: '📻',
      word: 'radio',
      disallowed: ['music', 'frequency', 'station', 'talk', 'communicate']
    },
    {
      emoji: '📱',
      word: 'phone',
      disallowed: ['call', 'text', 'technology', 'chat', 'apps']
    },
    {
      emoji: '⏰',
      word: 'alarm clock',
      disallowed: ['wake', 'sound', 'set', 'night', 'morning']
    },
    {
      emoji: '💻',
      word: 'computer',
      disallowed: ['work', 'type', 'technology', 'electronic', 'device']
    },
    {
      emoji: '🚪',
      word: 'door',
      disallowed: ['room', 'entrance', 'hinges', 'open', 'building']
    },
    {
      emoji: '🛏️',
      word: 'bed',
      disallowed: ['sleep', 'sheets', 'pillow', 'rest', 'mattress']
    },
    {
      emoji: '🧺⚙️',
      word: 'laundry machine',
      disallowed: ['clean', 'clothes', 'dirty', 'detergent', 'wash']
    },
    {
      emoji: '🧰',
      word: 'drill',
      disallowed: ['tool', 'spin', 'bit', 'hole', 'screw']
    },
    {
      emoji: '🪓',
      word: 'axe ',
      disallowed: ['sharp', 'chop', 'blade', 'tree', 'handle']
    },
    {
      emoji: '🧰',
      word: 'pliers',
      disallowed: ['tool', 'pinch', 'grip', 'cut', 'hold']
    },
    {
      emoji: '💍',
      word: 'necklace',
      disallowed: ['jewelry', 'chain', 'gold', 'silver', 'clasp']
    },
    {
      emoji: '💍',
      word: 'bracelet',
      disallowed: ['jewelry', 'wrist', 'arm', 'circle', 'bangle']
    },
    {
      emoji: '🦶💍',
      word: 'anklet',
      disallowed: ['jewelry', 'ankle', 'chain', 'clasp', 'wear']
    },
    {
      emoji: '🪧',
      word: 'sign',
      disallowed: ['symbol', 'indicator', 'show', 'tell', 'read']
    },
    {
      emoji: '🚦',
      word: 'intersection',
      disallowed: ['road', 'cross', 'car', 'stop', 'light']
    },
    {
      emoji: '🏡🚗',
      word: 'driveway',
      disallowed: ['car', 'house', 'garage', 'park', 'asphalt']
    },
    {
      emoji: '🪑🌅',
      word: 'porch swing',
      disallowed: ['house', 'outside', 'sit', 'chain', 'rock']
    },
    {
      emoji: '🔥🛁',
      word: 'hot tub',
      disallowed: ['jacuzzi', 'heat', 'bubbles', 'water', 'jets']
    },
    {
      emoji: '🕳',
      word: 'shovel',
      disallowed: ['tool', 'dig', 'handle', 'metal', 'scoop']
    },
    {
      emoji: '🏈⚽️',
      word: 'football',
      disallowed: ['game', 'sport', 'tackle', 'helmet', 'soccer']
    },
    {
      emoji: '🏒',
      word: 'hockey puck',
      disallowed: ['sport', 'ice', 'stick', 'goal', 'hit']
    },
    {
      emoji: '🪖',
      word: 'helmet',
      disallowed: ['head', 'brain', 'protect', 'safe', 'hard']
    },
    {
      emoji: '⚾️',
      word: 'baseball bat',
      disallowed: ['sport', 'ball', 'hit', 'wood', 'aluminum']
    },
    {
      emoji: '⚽️',
      word: 'soccer ball',
      disallowed: ['sport', 'field', 'goal', 'football', 'kick']
    },
    {
      emoji: '🛹',
      word: 'skateboard',
      disallowed: ['wheel', 'trick', 'trucks', 'deck', 'bearing']
    },
    {
      emoji: '🛼',
      word: 'roller skates ',
      disallowed: ['wheel', 'foot', 'shoe', 'laces', 'fast']
    },
    {
      emoji: '🏒',
      word: 'hockey stick',
      disallowed: ['sport', 'ice', 'puck', 'hit', 'hold']
    },
    {
      emoji: '⛳️',
      word: 'golf ball',
      disallowed: ['sport', 'hit', 'club', 'hole', 'white']
    },
    {
      emoji: '🏌️‍♀️',
      word: 'golf club',
      disallowed: ['sport', 'hit', 'ball', 'course', 'hole']
    },
    {
      emoji: '🎧',
      word: 'headphones',
      disallowed: ['ear', 'sound', 'music', 'play', 'listen']
    },
    {
      emoji: '🔊',
      word: 'stereo',
      disallowed: ['sound', 'music', 'play', 'listen', 'speaker']
    },
    {
      emoji: '⌚️',
      word: 'watch',
      disallowed: ['wrist', 'time', 'number', 'hand', 'strap']
    },
    {
      emoji: '📺🎛',
      word: 'remote control',
      disallowed: ['button', 'tv', 'television', 'device', 'wireless']
    },
    {
      emoji: '🪑🪑',
      word: 'table',
      disallowed: ['furniture', 'leg', 'surface', 'desk', 'sit']
    },
    {
      emoji: '🪑📚',
      word: 'desk',
      disallowed: ['furniture', 'leg', 'surface', 'work', 'sit']
    },
    {
      emoji: '🪑',
      word: 'chair',
      disallowed: ['furniture', 'leg', 'sit', 'seat', 'backrest']
    },
    {
      emoji: '💡💡',
      word: 'chandelier',
      disallowed: ['light', 'fixture', 'ceiling', 'bulb', 'electric']
    },
    {
      emoji: '👕🌀',
      word: 'dryer',
      disallowed: ['appliance', 'washer', 'clothes', 'wet', 'spin']
    },
    {
      emoji: '🪵🔥',
      word: 'fireplace',
      disallowed: ['wood', 'smoke', 'hot', 'chimney', 'flue']
    },
    {
      emoji: '🪞',
      word: 'mirror',
      disallowed: ['reflect', 'glass', 'silver', 'shiny', 'vanity']
    }
  ],
  hard_words: [
    {
      emoji: '🚒',
      word: 'firetruck',
      disallowed: ['emergency', 'fire', 'red', 'hydrant', 'siren']
    },
    {
      emoji: '🤨🤔',
      word: 'eyebrows ',
      disallowed: ['hair', 'shape', 'bushy', 'full', 'arched']
    },
    {
      emoji: '🧔',
      word: 'goatee',
      disallowed: ['beard', 'mustache', 'scruff', 'men', 'style']
    },
    {
      emoji: '🦠',
      word: 'virus',
      disallowed: ['microscopic', 'bacteria', 'germ', 'sickness', 'flu']
    },
    {
      emoji: '🕳️',
      word: 'black hole',
      disallowed: ['space', 'supermassive', 'gravity', 'star', 'light']
    },
    {
      emoji: '🌍🌡️',
      word: 'climate change',
      disallowed: ['weather', 'global warming', 'gases', 'hot', 'iceberg']
    },
    {
      emoji: '⛵',
      word: 'sailboat',
      disallowed: ['sea', 'wind', 'sail', 'rudder', 'sailor']
    },
    {
      emoji: '🏊',
      word: 'pool',
      disallowed: ['water', 'swim', 'dive', 'splash', 'float']
    },
    {
      emoji: '🌧️',
      word: 'rain',
      disallowed: ['droplets', 'wet', 'downpour', 'storm', 'hurricane']
    },
    {
      emoji: '🚓',
      word: 'police car',
      disallowed: ['patrol', 'protect', 'safety', 'blue', 'cop']
    },
    {
      emoji: '🚑',
      word: 'ambulance ',
      disallowed: ['car', 'hospital', 'care', 'help', 'medical']
    },
    {
      emoji: '🏥',
      word: 'hospital',
      disallowed: ['medical', 'doctor', 'sick', 'building', 'nurse']
    },
    {
      emoji: '🧑‍🚒🚒',
      word: 'fire station',
      disallowed: ['rescue', 'building', 'truck', 'red', 'emergency']
    },
    {
      emoji: '🛑',
      word: 'stop sign',
      disallowed: ['street', 'yield', 'safety', 'red', 'car']
    },
    {
      emoji: '🚦',
      word: 'streetlight ',
      disallowed: ['dark', 'lamp', 'road', 'safety', 'car']
    },
    {
      emoji: '🚒🔥',
      word: 'fire hydrant',
      disallowed: ['water', 'red', 'hose', 'truck', 'flames']
    },
    {
      emoji: '🛝',
      word: 'slide',
      disallowed: ['playground', 'child', 'park', 'game', 'fun']
    },
    {
      emoji: '🙈📊',
      word: 'monkey bars',
      disallowed: ['animal', 'playground', 'dangle', 'child', 'arms']
    },
    {
      emoji: '🤿🏊',
      word: 'diving board',
      disallowed: ['pool', 'splash', 'jump', 'water', 'swim']
    },
    {
      emoji: '🌼👩‍🌾',
      word: 'garden',
      disallowed: ['flower', 'grass', 'plants', 'trees', 'bushes']
    },
    {
      emoji: '🥸',
      word: 'mustache',
      disallowed: ['hair', 'beard', 'face', 'chin', 'wax']
    },
    {
      emoji: '🧔',
      word: 'beard',
      disallowed: ['hair', 'face', 'chin', 'comb', 'mustache']
    },
    {
      emoji: '🎤',
      word: 'drop the mic',
      disallowed: ['music', 'speech', 'release', 'floor', 'throw']
    },
    {
      emoji: '🗡️💅',
      word: 'slay',
      disallowed: ['yasss', 'awesome', 'beautiful', 'strong', 'work-it']
    },
    {
      emoji: '🌟✨',
      word: 'glow-up ',
      disallowed: ['transform', 'slay', 'change', 'better', 'appearance']
    },
    {
      emoji: '☕',
      word: 'tea',
      disallowed: ['gossip', 'earl grey', 'beverage', 'hot', 'rumor']
    },
    {
      emoji: '🧬',
      word: 'dna',
      disallowed: [
        'nucleotides',
        'blue-prints',
        'genetic-code',
        'base',
        'protein'
      ]
    },
    {
      emoji: '☄️',
      word: 'asteroid ',
      disallowed: ['space', 'rock', 'meteors', 'comets', 'dinosaur']
    },
    {
      emoji: '🚤',
      word: 'speedboat',
      disallowed: ['water', 'fast', 'fun', 'sport', 'motor']
    },
    {
      emoji: '🛶',
      word: 'canoe',
      disallowed: ['boat', 'river', 'paddle', 'transport', 'kayak']
    },
    {
      emoji: '🛶',
      word: 'kayak',
      disallowed: ['canoe', 'water', 'travel', 'paddle', 'boat']
    },
    {
      emoji: '🤿🐡',
      word: 'snorkel',
      disallowed: ['water', 'swim', 'fish', 'coral', 'goggles']
    },
    {
      emoji: '🛗',
      word: 'elevator',
      disallowed: ['up', 'down', 'doors', 'cables', 'building']
    },
    {
      emoji: '🛗🪜',
      word: 'escalator',
      disallowed: ['stairs', 'up', 'mall', 'down', 'fast']
    },
    {
      emoji: '🪜🚶',
      word: 'stairs',
      disallowed: ['steep', 'steps', 'winding', 'legs', 'wood']
    },
    {
      emoji: '🌧️🌳',
      word: 'rainforest',
      disallowed: ['humid', 'dark', 'green', 'tropical', 'jungle']
    },
    {
      emoji: '🧊🏔️',
      word: 'glacier',
      disallowed: ['cold', 'snow', 'ice', 'blue', 'frozen']
    },
    {
      emoji: '💡🏠',
      word: 'lighthouse',
      disallowed: ['white', 'beacon', 'warning', 'tall', 'light']
    },
    {
      emoji: '☁️💨',
      word: 'solar turbine',
      disallowed: ['power', 'sun', 'energy', 'electricity', 'environment']
    },
    {
      emoji: '⚡',
      word: 'lightning',
      disallowed: ['radiant', 'sky', 'bottle', 'gold', 'zeus']
    },
    {
      emoji: '🌪',
      word: 'tornado',
      disallowed: ['twister', 'destructive', 'weather', 'sky', 'dorothy']
    },
    {
      emoji: '🌀',
      word: 'hurricane',
      disallowed: ['destructive', 'wind', 'florida', 'rain', 'dangerous']
    },
    {
      emoji: '🌊',
      word: 'tsunami',
      disallowed: ['water', 'wave', 'island', 'earthquake', 'dangerous']
    },
    {
      emoji: '🏋️',
      word: 'dumbell',
      disallowed: ['weight', 'gym', 'lift', 'heavy', 'muscle']
    },
    {
      emoji: '👟',
      word: 'sneaker',
      disallowed: ['shoe', 'tennis', 'feet', 'sports', 'running']
    },
    {
      emoji: '🧘‍♀️',
      word: 'yoga',
      disallowed: ['studio', 'pose', 'stretch', 'namaste', 'class']
    },
    {
      emoji: '✂️',
      word: 'scissors',
      disallowed: ['cut', 'craft', 'sharp', 'blades', 'sheers']
    },
    {
      emoji: '👾💻',
      word: 'simulation',
      disallowed: ['theory', 'fake', 'replica', 'copy', 'clone']
    },
    {
      emoji: '🕓🚀',
      word: 'time travel',
      disallowed: ['science', 'fiction', 'future', 'past', 'wormhole']
    },
    {
      emoji: '👽',
      word: 'aliens ',
      disallowed: ['space', 'extraterrestrial', 'foreign', 'ship', 'universe']
    },
    {
      emoji: '🤖🧠',
      word: 'machine learning',
      disallowed: ['computer', 'science', 'intelligence', 'ai', 'data']
    },
    {
      emoji: '🌐⛓️',
      word: 'blockchain',
      disallowed: ['ledger', 'crypto', 'digital', 'distributed', 'coin']
    },
    {
      emoji: '🎿⛷️',
      word: 'snow ski ',
      disallowed: ['mountain', 'powder', 'boots', 'poles', 'lift']
    },
    {
      emoji: '🏄‍♀️',
      word: 'surf',
      disallowed: ['wave', 'ocean', 'board', 'sport', 'wetsuit']
    },
    {
      emoji: '🪂',
      word: 'parachute',
      disallowed: ['air', 'cloth', 'sky', 'fall', 'harness']
    },
    {
      emoji: '🏰',
      word: 'castle',
      disallowed: ['royal', 'king', 'queen', 'live', 'home']
    },
    {
      emoji: '🩻',
      word: 'x-ray',
      disallowed: ['doctor', 'bones', 'body', 'image', 'radiology']
    },
    {
      emoji: '💾',
      word: 'floppy disk',
      disallowed: ['data', 'storage', 'computer', 'magnetic', 'memory']
    },
    {
      emoji: '🖨️',
      word: 'printer',
      disallowed: ['ink', 'paper', 'cartridge', 'machine', 'font']
    },
    {
      emoji: '🧘‍♀️',
      word: 'yoga mat',
      disallowed: ['exercise', 'stretch', 'slip', 'pose', 'class']
    },
    {
      emoji: '🏃‍♀️',
      word: 'treadmill',
      disallowed: ['exercise', 'run', 'machine', 'belt', 'jog']
    },
    {
      emoji: '🏃‍♀️',
      word: 'elliptical machine',
      disallowed: ['exercise', 'gym', 'cardio', 'workout', 'pedals']
    },
    {
      emoji: '⏱',
      word: 'stop watch',
      disallowed: ['clock', 'time', 'hand', 'second', 'lap']
    },
    {
      emoji: '🙆‍♀️',
      word: 'stretch',
      disallowed: ['muscle', 'yoga', 'warm-up', 'flex', 'reach']
    },
    {
      emoji: '🪑⬆️',
      word: 'sit-up',
      disallowed: ['exercise', 'abs', 'muscle', 'crunch', 'lay']
    },
    {
      emoji: '🫖🔔',
      word: 'kettle bell',
      disallowed: ['exercise', 'weight', 'metal', 'swing', 'lift']
    },
    {
      emoji: '🧪🔗',
      word: 'glue',
      disallowed: ['stick', 'paste', 'adhesive', 'bond', 'seal']
    },
    {
      emoji: '🤖🤯',
      word: 'the singularity',
      disallowed: ['ai', 'artificial intelligence', 'future', 'robots', 'human']
    },
    {
      emoji: '💦',
      word: 'sprinkler',
      disallowed: ['water', 'spray', 'hose', 'fire', 'wet']
    },
    {
      emoji: '🤸‍♂️🦈',
      word: 'jump the shark',
      disallowed: ['culture', 'tv', 'movie', 'season', 'too far']
    },
    {
      emoji: '🚤⛷',
      word: 'water ski',
      disallowed: ['boat', 'pull', 'lake', 'tow', 'rope']
    },
    {
      emoji: '🚤🌊',
      word: 'wakeboard',
      disallowed: ['boat', 'pull', 'lake', 'wave', 'trick']
    },
    {
      emoji: '🛝',
      word: 'swing set',
      disallowed: ['playground', 'push', 'backyard', 'child', 'kid']
    }
  ]
}

export default allWords
