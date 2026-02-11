/* global window, document */

/**
 * Initialize Help Scout Beacon chat
 * @param {string} beaconId - The Help Scout Beacon ID
 * @param {Object} userData - Optional user data for identification
 */
export function initHelpScoutBeacon (beaconId, userData = null) {
  // Initialize the Beacon script
  ;(function (w, d, n) {
    function a () {
      const s = d.getElementsByTagName('script')[0]
      const b = d.createElement('script')
      b.type = 'text/javascript'
      b.async = true
      b.src = 'https://beacon-v2.helpscout.net'
      s.parentNode.insertBefore(b, s)
    }

    if (w.Beacon = n = function (t, n, a) {
      w.Beacon.readyQueue.push({ method: t, options: n, data: a })
    }, n.readyQueue = [], d.readyState === 'complete') {
      return a()
    }

    w.attachEvent ? w.attachEvent('onload', a) : w.addEventListener('load', a, false)
  })(window, document, window.Beacon || function () {})

  // Initialize with your Beacon ID
  window.Beacon('init', beaconId)

  window.Beacon('config', {
    display: {
      verticalOffset: 30,
      horizontalOffset: 25
    }
  })
  // If user data is provided, identify the user
  if (userData) {
    window.Beacon('identify', userData)
  }
}
