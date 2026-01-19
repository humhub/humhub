import axios from 'axios'
import IntlMessageFormat from 'intl-messageformat'

const detectLocale = () => {
  if (typeof document !== 'undefined') {
    const lang = document.documentElement.getAttribute('lang')
    if (lang && lang.trim()) {
        return lang;
    }
  }
  if (typeof navigator !== 'undefined' && navigator.language) {
      return navigator.language
  }
  return 'en'
}

let locale = detectLocale()
let globalMessages = {}
const loadedCategories = new Set()
const pendingLoads = new Map()
const compiledCache = new Map()

function compileMessage(template) {
  let perLocale = compiledCache.get(locale)
  if (!perLocale) {
    perLocale = new Map()
    compiledCache.set(locale, perLocale)
  }
  let formatter = perLocale.get(template)
  if (!formatter) {
      formatter = new IntlMessageFormat(template, locale)
    perLocale.set(template, formatter)
  }
  return formatter
}

function updateIntlMessages(newMessages) {
  globalMessages = {...globalMessages, ...newMessages}
}

export async function loadTranslations(category) {
  if (loadedCategories.has(category)) {
      return
  }
  if (pendingLoads.has(category)) {
      return pendingLoads.get(category)
  }

  const promise = (async () => {
    try {
      const {data} = await axios.get('/translation', {params: {category}})
      if (data) {
          locale = data.locale
          updateIntlMessages(data.messages)
          loadedCategories.add(category)
      }
    } finally {
      pendingLoads.delete(category)
    }
  })()

  pendingLoads.set(category, promise)
  return promise
}

export function translate(category, message, params = {}) {
    const key = String(message)
    const template = (globalMessages && key in globalMessages) ? globalMessages[key] : key
    return compileMessage(template).format(params)
}
