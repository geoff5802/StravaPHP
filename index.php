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
  <div ng-controller="ClubMember-Ctrl">
   <input type="text" ng-model="clubId" />
   <button ng-click="searchById(clubId)">button text</button>
    <ul class="member">
      <li ng-repeat="member in members | filter:query | orderBy:orderProp">
        <pre>{{member | json }}</pre>
          <ul class="member">
          <!--  <li ng-repeat="bike in members | filter:query | orderBy:orderProp">
              <p>{{members || json}}</p>
            </li>
          -->
          </ul>
      </li>
    </ul>
    
    <p>{{athleteDetail}}</p>
    
  </div>
</div>

<script>
  var access_token = '<?php echo $token ?>';
  console.log("my token:", access_token)
  var app = angular.module('myApp', ['ngResource']);
  
  app.service('stravaService', function($resource){
    
    var requestURL = 'https://www.strava.com/api/v3/:linkP1/:id/:linkP2/:linkP3?access_token=' + access_token;
    var requestParameters = {
         linkP1: '@linkP1',
         linkP2: '@linkP2',
         linkP3: '@linkP3',
         id: '@id'
    };

    var actions = {
      getStravaListArray:{
         method: 'JSONP',
         isArray: true,
         params: {
              callback: 'JSON_CALLBACK'
         }
      },
      getStravaListNoArray:{
         method: 'JSONP',
         isArray: false,
         params: {
              callback: 'JSON_CALLBACK'
         }
      }
    };
    var requestResource = $resource(requestURL, requestParameters, actions);
    
    var clubMembers = function(id) {
      var payload = {
        linkP1: 'clubs',
        linkP2: 'members',
        id: id
      };
      return requestResource.getStravaListArray(payload);
    };
    var athleteDetail = function(payload){
      payload = payload || {};
      payload.linkP1 = 'athletes';
      payload.id = '630743';
      return requestResource.getStravaListNoArray(payload);
    };
    return { 
      clubMembers: clubMembers,
      athleteDetail: athleteDetail 
    };
  });

  app.factory('stravaFactory', function(stravaService) {
    var clubMembers = function(payload){
      return stravaService.clubMembers(payload);
    }  
    var athleteDetail = function(){
      return stravaService.athleteDetail();
    }
    return { 
      clubMembers: clubMembers,
      athleteDetail: athleteDetail 
    };
  });
    
  app.controller('ClubMember-Ctrl', ['$scope','stravaFactory',
    function($scope, stravaFactory){
      function setScopeData(response, name){
        $scope[name] = angular.copy(response);
      }
      function errorHandler(error){
        console.log('error:', error);
      }
      function getClubMembers(payload){
        $scope.members = stravaFactory.clubMembers(payload);   
      }
      function getAthleteDetail(){
        $scope.athleteDetail = stravaFactory.athleteDetail(); 
      }
      
      /* 
      - Get authenticated athlete 
        - extract clubs 
        - build list of club id's and names behind a drop down
      /*
      
      $scope.clubId = '187498';
      
      $scope.searchById = function(id){
        return getClubMembers(id);
      };
      
      //init handler
      getClubMembers($scope.clubId);
      getAthleteDetail();
  }]);

</script>

</body>
</html>