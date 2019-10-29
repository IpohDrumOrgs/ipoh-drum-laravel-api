
Route::middleware('apilogger')->post('/test',function(){
    return response()->json("test");
});