/**
 * https://github.com/webdynamik/password-generator
 */
// passwort-generator.js
// http://passwort-generieren.de
// (c) 2014 Jan Krause
(function() {
    "use strict";
    var root = this;
    var PasswordGenerator = function(options) {
        if(!options){
            options = {};
            options.el = document.body;
        }
        this.options = this.extend(options, this.default_options);
    };
    // Export the object for **Node.js**
    if (typeof exports !== 'undefined') {
        if (typeof module !== 'undefined' && module.exports) {
            exports = module.exports = PasswordGenerator;
        }
        exports.PasswordGenerator = PasswordGenerator;
    } else {
        root.PasswordGenerator = PasswordGenerator;
    }
    PasswordGenerator.prototype = {
        options: {},
        default_options: {
            length: 12,
            lowercase: true,
            uppercase: true,
            numbers: true,
            special_character: true,
            brackets: true,
            minus: true,
            underscore: true,
            space: true
        },
        _passwort: '',
        extend: function(options,defaults){
            var extended = {};
            var prop;
            for (prop in defaults) {
                if (Object.prototype.hasOwnProperty.call(defaults, prop)) {
                    extended[prop] = defaults[prop];
                }
            }
            for (prop in options) {
                if (Object.prototype.hasOwnProperty.call(options, prop)) {
                    extended[prop] = options[prop];
                }
            }
            return extended;
        },
        generate: function() {
            var _i, _len, _passwortString = '';
            var input = document.getElementById('pwLen');
            if (this.options.lowercase){
                _passwortString += 'abcdefghijklmnopqrstuvwxyz';
            }
            if (this.options.uppercase){
                _passwortString += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            }
            if (this.options.numbers){
                _passwortString += '0123456789';
            }
            if (this.options.special_character){
                _passwortString += ',.;:#+~*=&%$??!|/???@""^??`??\\'; // \'
            }
            if (this.options.brackets){
                _passwortString += '<>[](){}';
            }
            if (this.options.minus){
                _passwortString += '-';
            }
            if (this.options.underscore){
                _passwortString += '_';
            }
            if (this.options.space){
                _passwortString += ' ';
            }
            this._passwort = '';
            this.options.length = ((this.options.length === '' )||(this.options.length < 8 ) ? 8 : this.options.length);
            input.value = this.options.length;
            for (_i = 0, _len = this.options.length; _i < _len; _i++) {
                this._passwort += _passwortString.charAt(Math.floor(Math.random() * _passwortString.length));
            }
        },
        set: function(param) {
            this.options = this.extend(param,this.options);
        },
        get: function() {
            this.generate();
            return this._passwort;
        },
        render: function() {
            this.options.el.value  = this.get();
            this.options.el2.value = ''; //this._passwort;
        }
    };

}.call(this));
