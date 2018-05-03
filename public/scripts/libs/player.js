// This library depend on the jwplayer.js library,
// needs to be load later
(function() {
        //jwplayer.key="FZ41xVwzupK3fp5vvpjN6GbXFfY0YAMrlZE8QQ==";
        jwplayer.key="Jge1RC/1+m2wBFYICUUVOyWrSxqP/WXcGG07rQ==";
	$.fn.setupPlayer = function (video, thumbnail)
	{
		var playerInstance = jwplayer(this[0].id);
		jwplayer().setup({
			autostart: 'false',
			flashplayer: './jwplayer-7.9.1/jwplayer.flash',
			file: video,
			image: thumbnail,
			rtmp: {
				bufferlength: 4,
			},
			controls: true,
			skin: {
				'name': '../../styles/libs/playerskin/glow',
			},
			//wmode: 'opaque',
			width: "100%",
			aspectratio: "16:9",
				/*
				events: {
					onComplete: function() {
						this.stop();
						window.clearTimeout(TimingPlay);
						this.setFullscreen(false);
					},
					onBeforePlay: function() {
						window.focus();
						window.clearTimeout(TimingPlay);
					},
					onReady: function() {
						videoObject = this;
						window.clearTimeout(TimingPlay);
						top.playerready= true;
						if(lecture) {
							TimingPlay = window.setTimeout(
									function() {
										if(videoObject) {
											if ('play' in videoObject) {
												videoObject.play();
											}
										}}, 300);
						}
						else {
							TimingPlay = window.setTimeout(
								function() {
									videoObject.stop();
									videoObject = false;
									top.document.getElementById('player').style.visibility = 'hidden';
									top.document.getElementById('player').style.width = '0px';
									top.document.getElementById('player').style.height = '0px';
									}, 100);
						}
					},
					onPlay: function() {
						window.focus();
						this.setFullscreen(false);
						window.clearTimeout(TimingPlay);
					},
					onPause: function() {
						window.focus();
						this.setFullscreen(false);
						window.clearTimeout(TimingPlay);
					},
					onSeek: function() {
						window.focus();
						window.clearTimeout(TimingPlay);
					},
					onVolume: function() {
						window.focus();
						window.clearTimeout(TimingPlay);
					},
					onFullscreen: function() {
						window.focus();
						window.clearTimeout(TimingPlay);
					}

				}*/
			});
		}

})();
