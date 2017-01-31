function Registry() {}

Registry.instance = null;

Registry.getInstance = function() {
    if (Registry.instance === null) {
        Registry.instance = new Registry();
    }
    return Registry.instance;
}

Registry.prototype._data = {};

Registry.prototype.set = function(key, value) {
    this._data[key] = value;
}

Registry.prototype.get = function(key) {
    return this._data[key];
}
    
Registry.prototype.save = function() {
    var jsonData = $.JSON.encode(this._data);
    Cookie.remove("ifresco-Registry");
    Cookie.save("ifresco-Registry",jsonData,30);
}

Registry.prototype.read = function() {
    var registryCookies = Cookie.get("ifresco-Registry");
    
    var jsonDecode = $.JSON.decode(registryCookies);
    if (jsonDecode != null)
        this._data = jsonDecode;
}