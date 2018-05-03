var responsive = {
	isMobile: "only screen and (max-width: 479px)",
	isMobileL: "only screen and (min-width: 480px) and (max-width: 900px)",
	isTablet: "only screen and (min-width: 901px) and (max-width: 1279px)",
	isDesktop: "only screen and (min-width: 1280px)",

	minMobile: "",
	minMobileL: "only screen and (min-width: 480px)",
	minTablet: "only screen and (min-width: 901px)",
	minDesktop: "only screen and (min-width: 1280px)",

	noMobile: "only screen and (min-width: 480px)",
	noMobileL: "only screen and (min-width: 901px), only screen and (max-width: 479px)",
	noTablet: "only screen and (min-width: 1280px), only screen and (max-width: 900px)",
	noDesktop: "only screen and (max-width: 1279px)",

	maxMobile: "only screen and (max-width: 479px)",
	maxMobileL: "only screen and (max-width: 900px)",
	maxTablet: "only screen and (max-width: 1279px)",
	maxDesktop: "",

	test: function(media){
		if(window.matchMedia){
			if(typeof this[media]==="undefined"){
				return false;
			} else {
				if(window.matchMedia(this[media]).matches){
					return true;
				} else {
					return false;
				}
			}
		}
	}
}

