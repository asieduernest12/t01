<?php

function parseJsonRide($ride)
{
    $waypoint_gpxs = array_map(function ($waypoint) {
        return wayPointToGpxTrkpt(
            $waypoint->position->coords->latitude,
            $waypoint->position->coords->longitude,
            $waypoint->utc_timestamp
        );
    }, $ride->waypoints); //get the waypoints as an array


    $waypoints_gpxs_str = implode("", $waypoint_gpxs);

    $trkseg = <<<TRKSEG
    <trkseg>
        $waypoints_gpxs_str
    </trkseg>
TRKSEG;

    $trk = <<<TRK
    <trk>
        <ride id="{$ride->id}" ></ride>
        <name>{$ride->name}</name>
        <type>some type</type>
        {$trkseg}
    </trk>
TRK;
    return $trk;
}


function wrapAsGpx($innerContent)
{
    return <<<GPXINNER
            <gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:gpxx="http://www.garmin.com/xmlschemas/GpxExtensions/v3" xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1" 
            creator="Oregon 400t" version="1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
            xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd">
            <metadata>
            <link href="http://www.garmin.com">
            <text>Garmin International</text>
            </link>
            <time>2009-10-17T22:58:43Z</time>
        </metadata>
        {$innerContent}

        </gpx>
GPXINNER;
}


function wayPointToGpxTrkpt($lat, $lon, $time)
{

    return <<<TRKPT
    <trkpt lat="{$lat}" long="{$lon}">
        <time>{$time}</time>
    </trkpt>
TRKPT;
}




function testParser()
{
    $ride_str = <<<RIDE
    {
        "id": 1,
        "waypoints": [
            {
                "utc_timestamp": "2022-04-18T21:11:21.486Z",
                "position": {
                    "coords": {
                        "longitude": -91.5414511,
                        "latitude": 41.6531178
                    }
                }
            },
            {
                "utc_timestamp": "2022-04-18T21:11:22.172Z",
                "position": {
                    "coords": {
                        "longitude": -91.5414511,
                        "latitude": 41.6531178
                    }
                }
            }
           
            
        ]
    }
RIDE;


    print($ride_str);
    $ride = json_decode($ride_str);
    var_dump($ride);
    print(parseJsonRide($ride));
}
