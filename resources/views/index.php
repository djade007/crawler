<!DOCTYPE html>
<html lang="en" >
<head>
    <title>Crawler</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no" />

    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:400,500,700,400italic'>
    <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/angular_material/1.0.5/angular-material.min.css"/>
    <link rel="stylesheet" href="assets/app.css"/>

    <style type="text/css">
        /**
         * Hide when Angular is not yet loaded and initialized
         */
        [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
            display: none !important;
        }
    </style>

</head>

<body ng-app="Crawler" layout="row" ng-cloak>

<div flex layout="column" tabIndex="-1" role="main" class="md-whiteframe-z2" ng-controller="PostController">

    <md-toolbar layout="row" class="md-whiteframe-z1">
        <h3>Search Engine</h3>
    </md-toolbar>

    <md-content flex layout-padding>
        <div layout layout-sm="column" class="input">
        <md-input-container flex>
            <label>Search</label>
            <input ng-model="q" ng-change="search()">
        </md-input-container>
        </div>
            <div layout="row" class="input">
                <div flex-xs flex="50">
                    <md-checkbox ng-model="nl" aria-label="Search Nairland" ng-change="search()">
                        Nairaland
                    </md-checkbox>
                </div>

                <div flex-xs flex="50">
                    <md-checkbox ng-model="stack" aria-label="Search StackOverFlow" ng-change="search()">
                        StackOverFlow
                    </md-checkbox>
                </div>
            </div>
        <md-content flex>
            <md-progress-linear md-mode="indeterminate" ng-show="loading"></md-progress-linear>
            <p ng-if="not == 1" class="empty">
                No result found...
            </p>
        </md-content>

        <md-content class="md-padding" layout-xs="column" layout="row">
            <div flex-xs flex-gt-xs="50" layout="column">
                <md-card ng-repeat="post in posts.col1">
                    <md-card-header>
                        <md-card-avatar>
                            <img ng-src="{{ post.type == 'nairaland' ? '/assets/nl.png' : '/assets/stack.png' }}" title="{{ post.type | uppercase }}"/>
                        </md-card-avatar>
                        <md-card-header-text>
                            <span class="md-title"><a target="_blank" class="md-primary" ng-href="{{ post.author.link }}">{{ post.author.name }}</a></span>
                            <span class="md-subhead">{{ post.tags }}</span>
                        </md-card-header-text>
                    </md-card-header>
                    <md-card-title>
                        <md-card-title-text>
                            <span class="md-headline"><a class="md-primary" ng-href="{{ post.link }}" target="_blank">{{ post.title }}</a></span>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <p ng-bind-html="highlight(post.content)"></p>
                    </md-card-content>
                    <md-card-actions layout="row" layout-align="end center">
                        <ul class="tags">
                            <small>
                                <li class="tag">VIEWS: {{ post.views }}</li>
                                <li class="tag" ng-repeat="(key, value) in post.data">
                                    {{ key | uppercase }} : {{ value }}
                                </li>
                            </small>
                        </ul>
                    </md-card-actions>
                </md-card>
            </div>
            <div flex-xs flex-gt-xs="50" layout="column">
                <md-card ng-repeat="post in posts.col2">
                    <md-card-header>
                        <md-card-avatar>
                            <img ng-src="{{ post.type == 'nairaland' ? '/assets/nl.png' : '/assets/stack.png' }}" title="{{ post.type | uppercase }}"/>
                        </md-card-avatar>
                        <md-card-header-text>
                            <span class="md-title"><a target="_blank" class="md-primary" ng-href="{{ post.author.link }}">{{ post.author.name }}</a></span>
                            <span class="md-subhead">{{ post.tags }}</span>
                        </md-card-header-text>
                    </md-card-header>
                    <md-card-title>
                        <md-card-title-text>
                            <span class="md-headline"><a class="md-primary" ng-href="{{ post.link }}" target="_blank">{{ post.title }}</a></span>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <p ng-bind-html="highlight(post.content)"></p>
                    </md-card-content>
                    <md-card-actions layout="row" layout-align="end center">
                        <ul class="tags">
                            <small>
                                <li class="tag">VIEWS: {{ post.views }}</li>
                                <li class="tag" ng-repeat="(key, value) in post.data">
                                    {{ key | uppercase }} : {{ value }}
                                </li>
                            </small>
                        </ul>
                    </md-card-actions>
                </md-card>
            </div>
        </md-content>
        <section layout="row" layout-align="center center" layout-wrap ng-if="posts.info.total > posts.info.per_page">
            <md-button md-no-ink class="md-primary" ng-disabled="posts.info.current_page == 1" ng-click="prev('l')">Previous</md-button>
            <md-button md-no-ink class="md-primary" ng-disabled="posts.info.current_page == posts.info.last_page" ng-click="next('l')">Next</md-button>
        </section>
    </md-content>

    <md-content>
        <p style="text-align: center; font-size: 85%">Developed by <a href="http://djade.net">DJADE</a></p>
    </md-content>

</div>

<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular-animate.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular-aria.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular-messages.min.js"></script>

<!-- Angular Material Library -->
<script src="http://ajax.googleapis.com/ajax/libs/angular_material/1.0.5/angular-material.min.js"></script>

<script src="./src/posts/PostController.js"></script>


<script type="text/javascript">

    angular
        .module('Crawler', ['ngMaterial', 'posts'])
        .config(function($mdThemingProvider){
            $mdThemingProvider.theme('default')
                .primaryPalette('teal')
                .accentPalette('orange');

        });

</script>

</body>
</html>
