// https://codebeautify.org/minify-js
class Map {
    constructor(element) {
        this.element = element
        this.map = null
        this.mapMarkers = []
    }

    readMarkers() {
        this.element.querySelectorAll('.acf-views__map-marker').forEach((markerElement) => {
            let lat = markerElement.dataset.hasOwnProperty('lat') ?
                markerElement.dataset['lat'] :
                0
            let lng = markerElement.dataset.hasOwnProperty('lng') ?
                markerElement.dataset['lng'] :
                0

            this.mapMarkers.push({
                lat: parseFloat(lat),
                lng: parseFloat(lng),
            })

            markerElement.remove()
        })
    }

    createMap() {
        let mapArgs = {
            zoom: parseInt(this.element.dataset.hasOwnProperty('zoom') ?
                this.element.dataset['zoom'] :
                16),
            mapTypeId: window.google.maps.MapTypeId.ROADMAP,
        }
        this.map = new window.google.maps.Map(this.element, mapArgs)
    }

    createMarkers() {
        this.mapMarkers.forEach((marker) => {
            new window.google.maps.Marker({
                position: marker,
                map: this.map,
            })
        })
    }

    centerMap() {
        // Create map boundaries from all map markers.
        let bounds = new window.google.maps.LatLngBounds()

        this.mapMarkers.forEach((marker) => {
            bounds.extend({
                lat: marker.lat,
                lng: marker.lng,
            })
        })

        if (1 === this.mapMarkers.length) {
            this.map.setCenter(bounds.getCenter())
        } else {
            this.map.fitBounds(bounds)
        }
    }

    init() {
        if (!window.hasOwnProperty('google') ||
            !window.google.hasOwnProperty('maps') ||
            !window.google.maps.hasOwnProperty('Map') ||
            !window.google.maps.hasOwnProperty('Marker') ||
            !window.google.maps.hasOwnProperty('LatLngBounds') ||
            !window.google.maps.hasOwnProperty('MapTypeId') ||
            !window.google.maps.MapTypeId.hasOwnProperty('ROADMAP')) {
            console.log('ACF Views : Google maps isn\'t available')
            return
        }

        // before the map initialization, because during creation HTML is changed
        this.readMarkers()
        this.createMap()
        this.createMarkers()
        this.centerMap()
    }
}

class Maps {
    constructor() {
        this.isMapsLoaded = false
        this.mapsToInit = []

        window.acfViewsGoogleMaps = this.mapsLoadedCallback.bind(this)

        'loading' !== document.readyState ?
            this.setup() :
            window.addEventListener('DOMContentLoaded', this.setup.bind(this))
    }

    setup() {
        const observer = new MutationObserver((records, observer) => {
            for (let record of records) {
                record.addedNodes.forEach((addedNode) => {
                    this.addListeners(addedNode)
                })
            }
        })
        observer.observe(document.body, {
            childList: true,
            subtree: true,
        })

        this.addListeners(document.body)
    }

    mapsLoadedCallback() {
        this.isMapsLoaded = true

        this.mapsToInit.forEach((map) => {
            map.init()
        })

        this.mapsToInit = []
    }

    addListeners(element) {
        if (Node.ELEMENT_NODE !== element.nodeType) {
            return
        }

        // some maps can be without the Google map, only with a text address
        // that's why create Map not on the .acf-views__field element, but exactly on .acf-views__map

        element.querySelectorAll('.acf-views__map').forEach((mapElement) => {
            let map = new Map(mapElement)

            if (!this.isMapsLoaded) {
                this.mapsToInit.push(map)

                return
            }

            map.init()
        })
    }

}

new Maps()