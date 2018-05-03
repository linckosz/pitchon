var Velocity_packagedEffects = {
	
	"bruno.expandIn": {
		defaultDuration: 700,
		calls: [
			[ { opacity: [ 1, 0 ], transformOriginX: [ "50%", "50%" ], transformOriginY: [ "50%", "50%" ], scaleX: [ 1, 0.75 ], scaleY: [ 1, 0.75 ], translateZ: 0 } ]
		]
	},
	
	"bruno.expandOut": {
		defaultDuration: 700,
		calls: [
			[ { opacity: [ 0, 1 ], transformOriginX: [ "50%", "50%" ], transformOriginY: [ "50%", "50%" ], scaleX: 0.8, scaleY: 0.8, translateZ: 0 } ]
		],
		reset: { scaleX: 1, scaleY: 1 }
	},
	
	"bruno.slideRightIn": {
		defaultDuration: 750,
		calls: [
			[ { translateX: [ 0, '100%' ] }, 1, { easing: "easeOutCirc" } ]
		]
	},
	
	"bruno.slideRightOut": {
		defaultDuration: 750,
		calls: [
			[ { translateX: [ '100%', 0 ] }, 1, { easing: "easeInCirc" } ]
		]
	},
	
	"bruno.slideRightBigIn": {
		defaultDuration: 800,
		calls: [
			[ { opacity: [ 1, 0 ], translateX: [ 0, '30%' ], translateZ: 0 } ]
		]
	},
	
	"bruno.slideRightBigOut": {
		defaultDuration: 750,
		calls: [
			[ { opacity: [ 0, 1 ], translateX: '30%', translateZ: 0 } ]
		],
		reset: { translateX: 0 }
	},

	"bruno.slideLeftIn": {
		defaultDuration: 750,
		calls: [
			[ { translateX: [ 0, '-100%' ] }, 1, { easing: "easeOutCirc" } ]
		]
	},
	
	"bruno.slideLeftOut": {
		defaultDuration: 750,
		calls: [
			[ { translateX: [ '-100%', 0 ] }, 1, { easing: "easeInCirc" } ]
		]
	},
	
	"bruno.slideLeftBigIn": {
		defaultDuration: 800,
		calls: [
			[ { opacity: [ 1, 0 ], translateX: [ 0, '-30%' ], translateZ: 0 } ]
		]
	},
	
	"bruno.slideLeftBigOut": {
		defaultDuration: 750,
		calls: [
			[ { opacity: [ 0, 1 ], translateX: [ '-30%', 0 ], translateZ: 0 } ]
		],
		reset: { translateX: 0 }
	},

	"bruno.fadeIn": {
		defaultDuration: 500,
		calls: [
			[ { opacity: [ 0.7, 1 ] } ]
		]
	},

};

for (var effectName in Velocity_packagedEffects) {
	$.Velocity.RegisterEffect(effectName, Velocity_packagedEffects[effectName]);
}
