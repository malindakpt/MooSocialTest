var NodeCache = require( "node-cache" );
var myCache = new NodeCache();
var _sqlCached = [];
var _userIndex = {};
var log = require("./chatLog");
var _findQueryCache = function(sql,userId){
    for(var i=0;i<_sqlCached.length;i++){
        if(_sqlCached[i] == sql ){
            return i;
        }
    }
    _sqlCached.push(sql);
    if(userId != 0){
        if(!_userIndex.hasOwnProperty(userId)) {
            _userIndex[userId] = [];
        }
        _userIndex[userId].push(_sqlCached.length-1);
    } //console.log("_findQueryCache "+userId,_userIndex);
    return _sqlCached.length-1;
};
module.exports =(function () {
    var query = function(sql,callbackHasCached,callbackNoCached,userId){
        userId = typeof userId !== 'undefined' ? userId:0;
        var key = 'query' + _findQueryCache(sql,userId);

        myCache.get( key , function( err, value ){  
            if( !err ){
                if(value == undefined){
                    callbackNoCached(key);
                }else{
                    callbackHasCached(err,value);
                }
            }
        });
    };
    var  set = function(key,value){
        
        myCache.set(key,value);
    };
    var emptyQuery = function(userId){
        userId = typeof userId !== 'undefined' ? userId:0;
        if(userId != 0){
            if(_userIndex.hasOwnProperty(userId)){
                if (Array.isArray(_userIndex['userId'])) {
                    for(var i=0;i<_userIndex['userId'].length;i++){
                        myCache.del('query'+_userIndex['userId'][i]);
                        _sqlCached[_userIndex['userId'][i]]="";
                    }
                    _userIndex['userId']=[];
                    //console.log("emptyQuery "+userId,_userIndex);
                    return 0;
                }
            }

        }

        for(var i=0;i<_sqlCached.length;i++){
            myCache.del('query'+i);
        }
        _sqlCached = [];
    };
    return {
        query:query,
        set:set,
        emptyQuery:emptyQuery
    }
}());

