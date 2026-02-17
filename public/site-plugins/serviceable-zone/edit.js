  function initMap() {
    var map = new google.maps.Map(document.getElementById('map'), {
        center: {
            lat: 16.691307, 
            lng: 74.244865
        },
        zoom: 10
    });

    // Initialize the drawing manager
    var drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: google.maps.drawing.OverlayType.POLYGON,
        drawingControl: true,
        drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: ['polygon']
        },
        polygonOptions: {
            editable: true,
            draggable: false
        }
    });

    drawingManager.setMap(map);

    // Check if there are existing coordinates (from a hidden input field or server-side data)
    var existingCoordinates = document.getElementById("co_ordinates").value;

    // If there are existing coordinates, draw the polygon
    if (existingCoordinates) {
        var coordinates = JSON.parse(existingCoordinates);  // Convert JSON string back to array

        // Create the polygon on the map
        var polygonCoords = coordinates.map(function(coord) {
            return { lat: parseFloat(coord[1]), lng: parseFloat(coord[0]) };
        });

          var polygon = new google.maps.Polygon({
            paths: polygonCoords,
            strokeColor: '#808080',  // Dark grey color
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#808080',  // Dark grey color
            fillOpacity: 0.35,
            editable: true  // Allow editing of the polygon
        });

        polygon.setMap(map);
    }

    // Listen for the polygon completion event
    google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
        if (event.type === google.maps.drawing.OverlayType.POLYGON) {
            var polygon = event.overlay;

            // Loop through the polygon's path to get coordinates in "longitude latitude" order
            const path = polygon.getPath();
            let coordinates = [];
            for (let i = 0; i < path.getLength(); i++) {
                const latLng = path.getAt(i);
                coordinates.push([latLng.lng(), latLng.lat()]);
            }

            // Convert coordinates array to JSON string and set it in the hidden input
            document.getElementById("co_ordinates").value = JSON.stringify(coordinates);

            // Disable drawing mode to prevent multiple polygons
            drawingManager.setDrawingMode(null); 
        }
    });
}

// Load the map when the window loads
window.onload = initMap;
$(document).ready(function () {
    $('#form-serviceable-zone-save').validate({
        ignore: "",
        rules: {
            serviceable_zone_name: {
                required: true,
                minlength: 2,
                maxlength: 150
            },
        },
        messages: {
            serviceable_zone_name: {
                required: "The serviceable zone name field is required.",
                minlength: "The serviceable zone name must be at least 2 characters long.",
                maxlength: "The serviceable zone name must not exceed 150 characters."
            },
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        },
        submitHandler: function (form) {
            form.submit();
        }
    });
});
