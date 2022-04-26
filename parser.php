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
        <ride id="{$ride->id}"></ride>
        <name>some name</name>
        <type>some type</type>
        {$trkseg}
    </trk>
TRK;
    return $trk;
}



function wayPointToGpxTrkpt($lat, $lon, $time)
{
    print("parsing waypoint");
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
