//http://stackoverflow.com/questions/18020265/object-create-not-supported-in-ie8
if (!Object.create) {
	Object.create = function(o, properties) {
		if (typeof o !== 'object' && typeof o !== 'function') throw new TypeError('Object prototype may only be an Object: ' + o);
		else if (o === null) throw new Error("This browser's implementation of Object.create is a shim and doesn't support 'null' as the first argument.");
		if (typeof properties != 'undefined') throw new Error("This browser's implementation of Object.create is a shim and doesn't support a second argument.");
		function F() {}
		F.prototype = o;
		return new F();
	};
}

//Polyfill of Date.now(), because of IE8-
if (!Date.now) {
	Date.now = function() { return new Date().getTime(); }
}


if (!Math.sign) {
	Math.sign = function(x) {
		x = +x;
		if (x === 0 || isNaN(x)) {
			return x;  
		}
		return x > 0 ? 1 : -1;
	};
}

//performance.now() Polyfill
function perfnow(event){"performance" in event||(event.performance={});var o=event.performance;event.performance.now=o.now||o.mozNow||o.msNow||o.oNow||o.webkitNow||Date.now||function(){return(new Date).getTime()}}perfnow(self);

//string.trim() is not supported below IE9
if (!String.prototype.trim) {
  String.prototype.trim = function() {
	return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
  };
}

//Polyfil of compareLocale
//Note: This is not a true polyfil since we have only one parameter, but it's enough for the current application
try {
	var localeCompare_test = 'a'.localeCompare('b');
	delete localeCompare_test;
} catch(event){
	String.prototype.localeCompare = function(other, locale) {
		var charA = null, charB = null, index = 0;
		while (charA === charB && index < 100) {
			if(!this.toString()[index] || !other[index]){
				break;
			}
			charA = this.toString()[index].toLowerCase();
			charB = other[index].toLowerCase();
			index++;
		}
		if(charA > charB){
			return 1;
		} else if(charB > charA){
			return -1;
		}
		return 0;
	}
}
