/*
	Another way to compress faster may be to use PNG compression tool
	https://github.com/ethankaminski/Canvas-Text-Compress-JS
*/

//performance.now() Polyfill
function perfnow(event){"performance" in event||(event.performance={});var o=event.performance;event.performance.now=o.now||o.mozNow||o.msNow||o.oNow||o.webkitNow||Date.now||function(){return(new Date).getTime()}}perfnow(self);

self.addEventListener("message", function(event){
	var object = event.data;
	if(object && object.action){
		var data = null;
		if(typeof object.data != "undefined"){
			data = object.data;
		}
		if(typeof webworker_operation[object.action] == 'function'){
			webworker_operation[object.action](data);
		}
	}
});

var webworker_operation = {

	libraries: {},

	//webworker.postMessage(JSON.stringify({action: 'test', data: 123,}));
	test: function(obj_data){
		self.postMessage({action: 'test', data: obj_data,});
	},

	importscript: function(link){
		//Make sure we only improt the library once only
		if(typeof this.libraries[link] == 'undefined'){
			this.libraries[link] = true;
			importScripts(link);
		}
	},

	//https://www.sitepoint.com/measuring-javascript-functions-performance/
	checkPerformance: function(){
		var letters = 'a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z';
		for (var i = 0; i < 30; i++) {
			letters = letters+',a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z';
		}
		letters = letters.split(',');
		var numbers = false;
		var t0 = performance.now();
		for (var i = 0; i < letters.length; i++) {
			var needle = letters[i];
			letters.forEach(function(element) {
				if (element.toLowerCase() === needle.toLowerCase()) {
					found = true;
				}
			});
		}
		var t1 = performance.now();
		var indice = t1 - t0;
		self.postMessage({action: 'indicePerformance', data: indice,});
	},

};

