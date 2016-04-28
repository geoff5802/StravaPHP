<?php 
// http://stackoverflow.com/questions/28138071/how-to-access-jsonp-data-from-angular-service-in-view-using-expressions

include 'vendor/autoload.php';

use Strava\API\OAuth;
use Strava\API\Exception;

use Pest;
use Strava\API\Client;
use Strava\API\Service\REST;

try {
    $options = array(
        'clientId'     => 10658,
        'clientSecret' => '47dc483d6615a061c89ebdae5d185273ebb90f1f',
        'redirectUri'  => 'http://localhost:8888'
    );
    $oauth = new OAuth($options);

    if (!isset($_GET['code'])) {
        print '<a href="'.$oauth->getAuthorizationUrl().'">connect</a>';
    } else {
        $token = $oauth->getAccessToken('authorization_code', array(
            'code' => $_GET['code']
        ));
      //  test($token);
    }
} catch(Exception $e) {
    print $e->getMessage();
}


function test($token){
  try { 
      $adapter = new Pest('https://www.strava.com/api/v3');
      $service = new REST($token, $adapter);  // Define your user token here..
      $client = new Client($service);

      $athlete = $client->getAthlete();
      print_r($athlete["id"]);
      print_r("bikes distance:");
      print_r($athlete["bikes"][0]["distance"]);
      print_r(count($athlete["bikes"]));
      $activities = $client->getAthleteActivities();
      $clubs = $client->getAthleteClubs();
      
      $club = $client->getClub(187498);
    //   print_r($club["name"]);     
    //  $koms = $client->getAthleteKom($athlete["id"], $page = null, $per_page = null);
    //  print_r($athlete["id"]);
       
  } catch(Exception $e) {
      print $e->getMessage();
  }
  
}

?>
<!DOCTYPE html>
<html lang="en-US">
<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.16/angular-resource.min.js"></script>
<body>

<div ng-app="myApp">
  
    <div ng-controller="CurrentAthleteData-Ctrl">
      <p>{{athlete | json}}</p>
    </div>
      <!--
    <div ng-controller="StravaActivities-Ctrl">
      <ul class="act">
        <li ng-repeat="act in activities | filter:query | orderBy:orderProp" class="thumbnail act-listing">
          <pre>{{activities | json }}</pre>
        </li>
      </ul>
    </div>
  -->
    <div ng-controller="AthleteData-Ctrl">
      <p>{{athlete | json}}</p>
    </div>


  <div ng-controller="ClubMember-Ctrl">
    <ul class="member">
      <li ng-repeat="member in members | filter:query | orderBy:orderProp">
        <pre>{{members[$index].firstname | json }}</pre>
      </li>
    </ul>
  </div>

</div>

<script>
  var access_token = '<?php echo $token ?>';
  console.log("my token:", access_token)

  var app = angular.module('myApp', ['ngResource']);

  app.factory('ClubMember', function($resource) {
      return $resource('https://www.strava.com/api/v3/clubs/187498/members?access_token=' + access_token, {}, {
           getJSONP: {
            method: 'JSONP',
            isArray: true,
            params: {
                 callback: 'JSON_CALLBACK'
            }
          }
       });
    });
  app.controller('ClubMember-Ctrl', ['$scope', 'ClubMember',
    function($scope, ClubMember) {
     $scope.members = ClubMember.getJSONP();
  }]);

app.factory('StravaActivities', function($resource) {
    return $resource('https://www.strava.com/api/v3/athlete/activities?access_token=' + access_token, {}, {
         getJSONP: {
          method: 'JSONP',
          isArray: true,
          params: {
               callback: 'JSON_CALLBACK'
          }
        }
     });
  });
  app.controller('StravaActivities-Ctrl', ['$scope', 'StravaActivities',
    function($scope, StravaActivities) {
     $scope.activities = StravaActivities.getJSONP();
  }]);
  
  app.factory('CurrentAthleteData', function($resource) {
      return $resource('https://www.strava.com/api/v3/athlete?access_token=' + access_token, {}, {
           getJSONP: {
            method: 'JSONP',
            isArray: false,
            params: {
                 callback: 'JSON_CALLBACK'
            }
          }
       });
    });
  app.controller('CurrentAthleteData-Ctrl', ['$scope', 'CurrentAthleteData',
    function($scope, CurrentAthleteData) {
     $scope.athlete = CurrentAthleteData.getJSONP();
  }]);
  
  app.factory('AthleteData', function($resource) {
      return $resource('https://www.strava.com/api/v3/athletes/1939427?access_token=' + access_token, {}, {
           getJSONP: {
            method: 'JSONP',
            isArray: false,
            params: {
                 callback: 'JSON_CALLBACK'
            }
          }
       });
    });
  app.controller('AthleteData-Ctrl', ['$scope', 'AthleteData',
    function($scope, AthleteData) {
     $scope.athlete = AthleteData.getJSONP();
  }]); 
    
</script>

</body>
</html>