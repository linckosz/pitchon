$.extend(wrapper_date.prototype, {

	Constructor: function(timestamp_ms){
		if(typeof timestamp_ms != 'number'){
			this.time = new Date();
			this.timestamp_ms = this.time.getTime();
		} else {
			this.timestamp_ms = parseInt(timestamp_ms, 10);
			this.time = new Date(this.timestamp_ms);
		}
		this.timezone_offset_ms = this.time.getTimezoneOffset()*60*1000;
		this.server_offset_ms = 0; //Positive means the server has an earlier date
	},

	setTime: function(timestamp_ms){
		this.Constructor(timestamp_ms);
	},

	getTime: function(){
		return this.time;
	},

	//Timezone Offset in minutes
	getTimeOffset: function(){
		return this.timezone_offset_ms;
	},

	setServerOffset: function(server_ms){
		server_ms = parseInt(server_ms, 10);
		timestamp_ms = (new Date()).getTime();
		this.server_offset_ms = server_ms - timestamp_ms;
	},

	//A very close timestamp_ms of the server (php time). Note: few ms fewer because of internet communication time
	getTimeServer: function(){
		return (new Date()).getTime() + this.server_offset_ms;
	},

	//returns the timestamp_ms of the end of day (23:59:59:00) based on this.timestamp_ms
	getEndofDay: function(){
		var endofday = new Date(this.timestamp_ms).setHours(23, 59, 59, 0);
		return endofday;
	},

	//Return literal day
	getStrDay: function(){
		var time = this.getTime();
		var day = time.getDay();
		day = this.day[day];
		return day;
	},

	//Return literal short day
	getStrDayShort: function(){
		var time = this.getTime();
		var day_short = time.getDay();
		day_short = this.day_short[day_short];
		return day_short;
	},

	//Return literal month
	getStrMonth: function(){
		var time = this.getTime();
		var month = time.getMonth();
		month = this.month[month];
		return month;
	},

	//Return literal short month
	getStrMonthShort: function(){
		var time = this.getTime();
		var month_short = time.getMonth();
		month_short = this.month_short[month_short];
		return month_short;
	},

	//Return 2 digits year
	getYear: function(){
		var time = this.getTime();
		var year = ''+time.getFullYear();
		year = year.substr(-2);
		return year;
	},
	//Return 2 digits month
	getMonth: function(){
		var time = this.getTime();
		var month = '0' + (1+time.getMonth());
		month = month.substr(-2);
		return month;
	},

	//Return 2 digits date
	getDate: function(){
		var time = this.getTime();
		var date = '0' + time.getDate();
		date = date.substr(-2);
		return date;
	},

	//Return 2 digits hours
	getHours: function(){
		var time = this.getTime();
		var hours = '0' + time.getHours();
		hours = hours.substr(-2);
		return hours;
	},

	//Return 2 digits hours in US 12-H format
	getHoursUS: function(){
		var time = this.getTime();
		var hours = time.getHours()% 12 || 12;
		hours = '0' + hours;
		hours = hours.substr(-2);
		return hours;
	},

	//Return AM/PM of 12-H clock
	getAMPM: function(){
		var time = this.getTime();
		var ampm = time.getHours();
		ampm = ampm >= 12 ? 'PM' : 'AM';
		return ampm;
	},

	//Return 2 digits minutes
	getMinutes: function(){
		var time = this.getTime();
		var minutes = '0' + time.getMinutes();
		minutes = minutes.substr(-2);
		return minutes;
	},

	//Return 2 digits seconds
	getSeconds: function(){
		var time = this.getTime();
		var seconds = '0' + time.getSeconds();
		seconds = seconds.substr(-2);
		return seconds;
	},

	//Return ordinal suffix
	getOrdinal: function(){
		var time = this.getTime();
		var ordinal = time.getDate();
		ordinal = this.ordinal[ordinal];
		return ordinal;
	},

	//Return ordinal suffix in HTML superstring
	getOrdinalSuper: function(){
		var time = this.getTime();
		var ordinal = time.getDate();
		ordinal = '<sup>'+this.ordinal[ordinal]+'</sup>';
		return ordinal;
	},

	display: function(format){
		if(typeof format === 'string' && this.format[format]){
			format = this.format[format];
		} else {
			format = this.format['date_short']; //date_short [Jul 8th]
		}
		format = this.regex(format);
		return format;
	},

	happensToday: function() {
		var dateStampToday = new Date().setHours(0, 0, 0, 0); //timestamp_ms of beginning of the day taking in account the timezone
		var dateStampTomorrow = dateStampToday + (86400*1000);
		if ((this.timestamp_ms >= dateStampToday) && (this.timestamp_ms < dateStampTomorrow)){
			return true;
		} else {
			return false;
		}
	},

	//happensSomeday(-1) for yesterday
	//happensSomeday(1) for tomorrow
	happensSomeday: function(int) {
		var now = new Date;
		now.setHours(0);
		now.setMinutes(0);
		now.setSeconds(0);
		now.setMilliseconds(0);
		var dateStampPrev = now; //timestamp_ms of beginning of the day taking in account the timezone
		if(int != 0){
			dateStampPrev += (int*86400*1000);
		}
		var dateStampNext = dateStampPrev + (86400*1000);
		if ((this.timestamp_ms >= dateStampPrev) && (this.timestamp_ms < dateStampNext)){
			return true;
		} else {
			return false;
		}
	},

	getDayStartTimestamp: function(timestamp_ms) {
		if(typeof timestamp_ms == 'undefined'){
			timestamp_ms = this.timestamp_ms;
		}
		var now = new Date(timestamp_ms);
		now.setHours(0);
		now.setMinutes(0);
		now.setSeconds(0);
		now.setMilliseconds(0);
		return now; //timestamp_ms of the day taking in account the timezone
	},

	//Transform the format sentence into a readable date
	regex: function(format){
		var that = this;
		format = format.replace(/(\[\S+?\])|(\{\S+?\})/gi, function(match, internal, external){
			if(internal){
				internal = internal.replace(/[\[\]]/gi, '');
				if(typeof that[internal] === 'function'){
					internal = that[internal]();
				} else {
					internal = '';
				}
				return internal;
			} else if(external){
				external = external.replace(/[\{\}]/gi, '');
				if(typeof that.time[external] === 'function'){
					if(external=='getMonth'){ //Because Month in JS starts from 0
						external = parseInt(that.time[external](), 10)+1;
					} else {
						external = that.time[external]();
					}
				} else {
					external = '';
				}
				return external;
			}
			return '';

		});
		return format;
	},


});

Bruno.now = new wrapper_date();
