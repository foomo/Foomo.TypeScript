/// <reference path="../alsoBar/also.ts" />
/// <reference path="bar.ts" />
/// <reference path="test/nestedBar.ts" />

var foo = new Mock.Bar.BarTest(new Mock.Bar.Test.DeepTest());
console.log("debug is " + <?= $debug?'"On"':'"Off"' ?>);