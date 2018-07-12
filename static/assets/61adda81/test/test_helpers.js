var zeropad = function (num) {
  return ((num < 10) ? '0' : '') + num;
};
var iso8601 = function (date) {
  return date.getUTCFullYear()
    + "-" + zeropad(date.getUTCMonth()+1)
    + "-" + zeropad(date.getUTCDate())
    + "T" + zeropad(date.getUTCHours())
    + ":" + zeropad(date.getUTCMinutes())
    + ":" + zeropad(date.getUTCSeconds()) + "Z";
};

function prepareDynamicDates() {
  $('time.loaded').attr("datetime", iso8601(new Date()));
  $('time.modified').attr("datetime", iso8601(new Date(document.lastModified)));
}

function loadNumbers() {
  jQuery.timeago.settings.strings.numbers = ["zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine"];
}
function unloadNumbers() {
  jQuery.timeago.settings.strings.numbers = [];
}

function loadCutoffSetting() {
  jQuery.timeago.settings.cutoff = 7*24*60*60*1000;
}

function unloadCutoffSetting() {
  jQuery.timeago.settings.cutoff = 0;
}

function setupDisposal() {
  jQuery.timeago.settings.refreshMillis = 50;
  $('abbr.disposal').attr("title", iso8601(new Date())).timeago();
}

function loadPigLatin() {
  jQuery.timeago.settings.strings = {
    suffixAgo: "ago-hay",
    suffixFromNow: "omNow-fray",
    seconds: "ess-lay an-thay a-hay inute-may",
    minute: "about-hay a-hay inute-may",
    minutes: "%d inutes-may",
    hour: "about-hay an-hay hour-hay",
    hours: "about-hay %d hours-hay",
    day: "a-hay ay-day",
    days: "%d ays-day",
    month: "about-hay a-hay onth-may",
    months: "%d onths-may",
    year: "about-hay a-hay ear-yay",
    years: "%d years-yay"
  };
}

function loadRussian() {
  (function() {
    function numpf(n, f, s, t) {
      // f - 1, 21, 31, ...
      // s - 2-4, 22-24, 32-34 ...
      // t - 5-20, 25-30, ...
      var n10 = n % 10;
      if ( (n10 === 1) && ( (n === 1) || (n > 20) ) ) {
        return f;
      } else if ( (n10 > 1) && (n10 < 5) && ( (n > 20) || (n < 10) ) ) {
        return s;
      } else {
        return t;
      }
    }

    jQuery.timeago.settings.strings = {
      prefixAgo: null,
      prefixFromNow: "через",
      suffixAgo: "назад",
      suffixFromNow: null,
      seconds: "меньше минуты",
      minute: "минуту",
      minutes: function(value) { return numpf(value, "%d минута", "%d минуты", "%d минут"); },
      hour: "час",
      hours: function(value) { return numpf(value, "%d час", "%d часа", "%d часов"); },
      day: "день",
      days: function(value) { return numpf(value, "%d день", "%d дня", "%d дней"); },
      month: "месяц",
      months: function(value) { return numpf(value, "%d месяц", "%d месяца", "%d месяцев"); },
      year: "год",
      years: function(value) { return numpf(value, "%d год", "%d года", "%d лет"); }
    };
  })();
}

function resetRefreshMillis() {
  jQuery.timeago.settings.refreshMillis = 60000;
}

function loadMillis() {
  var millisSubstitution = function(number, millis) { return millis + " milliseconds"; };
  jQuery.timeago.settings.strings = {
    suffixAgo: "ago",
    suffixFromNow: "from now",
    seconds: millisSubstitution,
    minute: millisSubstitution,
    minutes: millisSubstitution,
    hour: millisSubstitution,
    hours: millisSubstitution,
    day: millisSubstitution,
    days: millisSubstitution,
    month: millisSubstitution,
    months: millisSubstitution,
    year: millisSubstitution,
    years: millisSubstitution
  };
}

function loadNoSpaces() {
  jQuery.extend(jQuery.timeago.settings.strings, {
    minutes: "%dminutes",
    wordSeparator: ""
  });
}

function loadNullSpaces() {
  jQuery.extend(jQuery.timeago.settings.strings, {
    minutes: "%dminutes",
    wordSeparator: null
  });
}

function loadYoungOldYears() {
  jQuery.extend(jQuery.timeago.settings.strings, {
    years: function(value) { return (value < 21) ? "%d young years" : "%d old years"; }
  });
}

function loadDoNotAllowFuture() {
  var mockDateToUse = "2010-01-01";
  $.timeago.settings.allowFuture = false;
  enableMockedDate(mockDateToUse);
}

function unloadDoNotAllowFuture() {
  $.timeago.settings.allowFuture = true;
  disableMockedDate();
}

function loadDoNotAllowPast() {
  var mockDateToUse = "2010-01-01";
  $.timeago.settings.allowFuture = true;
  $.timeago.settings.allowPast = false;
  $.timeago.settings.strings.inPast = "in the past";
  enableMockedDate(mockDateToUse);
}

function unloadDoNotAllowPast() {
  $.timeago.settings.allowFuture = true;
  $.timeago.settings.allowPast = true;
  disableMockedDate();
}

function enableMockedDate(dateToReturn) {
  var mockDate = dateToReturn;
  window.NativeDate = Date;
  window.Date = function () {
    if(arguments.length === 0) {
      return new window.NativeDate(mockDate);
    } else if(arguments.length === 1) {
      return new window.NativeDate(arguments[0]);
    } else  if(arguments.length === 7) {
      return new window.NativeDate(arguments[0], arguments[1], arguments[2], arguments[3], arguments[4], arguments[5], arguments[6]);
    } else {
      throw "Mocking Date with this number of parameters is not implemented.";
    }
  };
}

function disableMockedDate() {
  window.Date = window.NativeDate;
  delete window.NativeDate;
}
