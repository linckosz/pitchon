Pinyin.getPinyin=function(l1){ 
    var l2 = l1.length; 
    var I1 = ""; 

    for(var i=0; i<l2; i++){ 
        var val = l1.substr(i,1); 

        var name = false;
        for(var py in this._pinyin){
	        if(this._pinyin[py].indexOf(val)!=-1){
	            name = py; break; 
	        } 
	    }

        if( name !== false ){ 
            I1 += name.toLowerCase();
        } else {
        	I1 += val;
        }
    }

    return I1; 
}

Pinyin.GetQP=function(l1){
    return Pinyin.getPinyin(l1);
}
