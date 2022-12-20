/*
* Floatbox 8.5.2 - 2021-10-05
* Copyright (c) 2021 Byron McGregor
* License: MIT (see LICENSE.txt for details)
* Website: https://floatboxjs.com/
*/

( function () {
	var version = '8.5.2',
		build = '2021-10-05',
		self = window,
		document = self.document,
		parentView = self.parent != self && getOwnerView( self.parent ) || {},  // not x-domain
		fb = self.fb || {},
		data = fb.data || {},
		items = [],
		isArray = Array.isArray,
		now = Date.now,
		undef = void 0,
		smallScreen = isSmallScreen(),
		pageOptions = self.fbOptions || {},
		settings = {},  // will contain member objects of base, type, and class settings
		deferQueue = [],  // defer() calls served by core
		docReadyQueue = [],  // pending fb.docReady functions
		fbReadyQueue = isArray( pageOptions.ready ) ? pageOptions.ready : [],  // pending fb.ready functions
		requireCallbacks = {},  // callback queue for fb.require
		requireDone = {},  // record of what require has already fetched
		timeouts = {},  // for setTimer/clearTimer
		locationUrl = parseUrl( self.location.href ),
		scriptUrl = parseUrl( pageOptions.scriptPath ||
			( select( 'script[src*="floatbox.js"]' )[ 0 ] || select( 'script', 0, -1 ) ).src ),
		fbPath = scriptUrl.baseUrl,
		rexSpace = /\s+/g,
		floatboxClass = 'floatbox',  // default, may be updated in init
		docIsReady = false;  // set/corrected in docReady

///  begin api functions

	function docReady ( func ) {

	// Register functions to be fired after DOMContentLoaded.

		docReadyQueue.push( func );
		docIsReady = docIsReady || document.readyState != 'loading';

		if ( docIsReady ) {
			while ( docReadyQueue.length ) {
				trigger( docReadyQueue.shift() );  // called in order, synchronous
			}
		}
	}  // docReady


	function ready ( func, deferred ) {
	// Similar to docReady, except fires later when core is present.
	// Also handles defer queuing.

		( deferred ? deferQueue : fbReadyQueue ).push( func );

		if ( data.fbIsReady && docIsReady ) {  // fbIsReady set in coreInit()

			// deferQueue first because fb is not really ready until the deferred have run
			fbReadyQueue = deferQueue.concat( fbReadyQueue );
			deferQueue.length = 0;

			while ( fbReadyQueue.length ) {
				trigger( fbReadyQueue.shift() );  // called in order, synchronous
			}
		}
	}  // ready


	function extend () {
	// Shallow copy of property:value pairs between objects.
	// Passing in only one object replicates it.
	// side-effect: Modifies first passed object if more than one provided.
	// Returns concatenated object.
		var arg, prop, i,
			args = [].slice.call( arguments ),
			target = args.length > 1 && args.shift() || {};

		for ( i = 0; i < args.length; i++ ) {
			arg = args[ i ];
			for ( prop in arg ) {
				if ( arg[ prop ] !== undef ) {
					target[ prop ] = arg[ prop ];
				}
			}
		}
		return target;
	}  // extend


	function typeOf ( thing, compare ) {
	// Returns corrected typeof result or boolean compare against a type or array of types.
		var rtn, view,
			type = typeof thing;

		try {
			// scalars: boolean, number, string
			if ( !!thing === thing || +thing === thing || ''+thing === thing ) {
				rtn = type;
			}
			// empties: null, undefined, NaN
			else if ( !thing ) {
				rtn = ''+thing;
			}
			// host objects
			else if ( thing == thing.window ) {  // present but != for x-domain window objects
				rtn = 'window';
			}
			else if ( ( view = getOwnerView( thing ) ) ) {
				rtn = thing == view.document ? 'document' : 'node';
			}
		}
		catch ( _ ) { }

		if ( !rtn ) {
			// non-scalar, non-host
			rtn = ( /\s(\w+)/ ).exec( ( {} ).toString.call( thing ) )[ 1 ].toLowerCase();
		}

		if ( compare ) {
			rtn = ( isArray( compare ) ? compare : [ compare ] ).indexOf( rtn ) > -1;
		}

		return rtn;
	}  // typeOf


	function isSmallScreen () {
	// Boolean, usually true if screen is less than 8 inches.
		var rtn,
			parentSays = parentView.fb && parentView.fb.smallScreen,
			media = window.matchMedia;

		if ( !!parentSays === parentSays ) {
			rtn = parentSays;
		}
		else if ( media && window.innerWidth ) {
			rtn = !media( '(min-width:512px)' ).matches || !media( '(min-height:512px)' ).matches;
		}
		return !!rtn;
	}  // isSmallScreen


	function $ ( id, doc ) {
	// Extends getElementById to also look in the parent and child iframes.
		var iframes, i,
			rtn = id,  // reflect back passed in dom elements
			docs = [];  // documents to search

		if ( id && typeof id == 'string' ) {
			id = patch( id, '#', '' );  // accept 'id' or '#id'
			rtn = null;

			if ( doc ) {  // look only on the requested doc
				docs.push( doc );
			}

			else {

				docs.push( document );
				if ( parentView.document ) {
					docs.push( parentView.document );
				}

				iframes = select( 'iframe' );
				for ( i = 0; i < iframes.length; i++ ) {
					if ( ( doc = iframes[i].contentDocument ) ) {  // filters x-domain
						docs.push( doc );
					}
				}
			}

			for ( i = 0; i < docs.length && !rtn; i++ ) {
				rtn = docs[i].getElementById( id );
			}
		}
		return rtn;
	}  // $


	function select ( selector, $baseEl, nth ) {
	selector = isArray( selector ) ? selector.join( ',' ) : ''+selector;
	$baseEl = $( $baseEl );
	// document.querySelectorAll wrapper which always returns an array
	// and can pull out a single element.
		var rtn, scope, baseId, tempId,
			doc = ( getOwnerView( $baseEl ) || self ).document;

		if ( getTagName( $baseEl ) ) {  // request for only child nodes
			baseId = attr( $baseEl, 'id' );
			tempId = baseId || 'fb' + now();
			$baseEl.id = tempId;
			scope = '#' + tempId + ' ';
			selector = scope + patch( selector, /,/g, ',' + scope );
		}

		try {  // in case of selector syntax error
			rtn = [].slice.call(  // return array, not nodeList
				doc.querySelectorAll( selector )
			);
		}
		catch ( _ ) {
			rtn = [];
		}

		if ( scope ) {
			attr( $baseEl, 'id', baseId );
		}

		if ( +nth === nth ) {
			// numeric index request for single element (-ve from end)
			rtn = rtn[ nth < 0 ? nth + rtn.length : nth ];
		}
		return rtn;
	}  // select


	function require ( source, callback, refetch ) {
	// Build a dynamic script element to load a remote .js file
	// or to execute a textual js code fragment.
		var $script, type, guid, evt, i;

		if ( isArray( source ) ) {
			for ( i = 0; i < source.length; i++ ) {
				require( source[ i ], undef, refetch );
			}
			require( undef, callback );  // after all requested scripts are done
		}

		else if ( ( $script = $( 'fb' + source, document ) ) ) {
			// internal callback and cleanup for a completed script
			placeElement( $script );
			trigger( requireCallbacks[ source ] );
			deleteProp( requireCallbacks, source );
		}

		else if ( source && +source !== source && ( refetch || !requireDone[ source ] ) ) {
			// standard user call
			type = 'src';
			requireDone[ source ] = true;
		}

		else if ( callback ) {
			// process callbacks not handled through requireCallbacks
			if ( ''+callback === callback ) {  // 'eval' string
				type = 'text';
			}
			else {  // execute the others directly
				trigger( callback );
			}
		}

		if ( type ) {
			// build a new script element
			guid = now();
			$script = newElement( 'script' );
			$script.id = 'fb' + guid;

			if ( type == 'src' ) {
				$script.async = false;
				evt = addEvent( $script, [ 'load', 'error' ],
					function () {
						removeEvent( evt );
						require( guid );
					}
				);
				requireCallbacks[ guid ] = callback;
			}

			else {  // text
				source = callback + ';fb.require(' + guid + ')';
			}

			$script[ type ] = source;
			placeElement( $script, document.head );
		}
	}  // require


	function addEvent ( $el, action, func, capture, rtn ) {
	// Attach an event handler to a node.
	// Does DOM 0 events if action is on*.
	// Returns an array of arguments that can be sent to removeEvent.
		var i;

		if ( ( $el = $( $el ) || select( $el ) ) ) {
			rtn = rtn || [ $el, action, func, capture ];  // retain original values before recursion

			if ( isArray( $el ) ) {
				i = $el.length;
				while ( i-- ) {
					addEvent( $el[ i ], action, func, capture, rtn );
				}
			}

			else if ( isArray( action ) ) {
				i = action.length;
				while ( i-- ) {
					addEvent( $el, action[ i ], func, capture, rtn );
				}
			}

			else {  // single element, single action
				if ( $el.addEventListener && !/^on/.test( action ) ) {
					$el.addEventListener( action, func, capture );
				}
				else {
					$el[ action ] = func;  // DOM 0
				}
			}
		}
		return rtn;
	}  // addEvent


	function removeEvent ( $el, action, func, capture ) {
	// Remove an event handler from a node
	// or a bunch of them bundled by addEvent.
		var i;

		if ( ( $el = $( $el ) || select( $el ) ) ) {

			if ( func ) {

				if ( isArray( $el ) ) {
					i = $el.length;
					while ( i-- ) {
						removeEvent( $el[ i ], action, func, capture );
					}
				}

				else if ( isArray( action ) ) {
					i = action.length;
					while ( i-- ) {
						removeEvent( $el, action[ i ], func, capture );
					}
				}

				else {
					if ( $el.removeEventListener && !/^on/.test( action ) ) {
						$el.removeEventListener( action, func, capture );
					}
					else {
						$el[ action ] = undef;  // DOM 0
					}
				}
			}

			else if ( isArray( $el ) ) {

				if ( !$el[ 2 ] || isArray( $el[ 2 ] ) ) {
					// a bundle of arrays from addEvent
					i = $el.length;
					while ( $el.length ) {
						removeEvent( $el.pop() );  // will zero the array
					}
				}

				else {
					// a single [el,action,func] array
					removeEvent.apply( self, $el );
				}
			}
		}
	}  // removeEvent


	function stopEvent ( e, stopPropagation, preventDefault ) {
	// Stop event propagation and default action.
	// defaults: stopPropagation=false, preventDefault=true

		if ( e ) {
			if ( stopPropagation ) {
				if ( e.stopPropagation ) {
					e.stopPropagation();
				}
			}

			if ( preventDefault !== false ) {
				if ( e.preventDefault ) {
					e.preventDefault();
				}
			}
		}
	}  // stopEvent


	function getClass ( thing ) {
	thing = $( thing );
	// Returns all existing class names in an array.
	// 'thing' param can be an element, element id,
	// or any object that has a className property.
		var rtn = [];

		if ( thing ) {
			if ( thing.classList ) {
				rtn = [].slice.call( thing.classList );
			}
			else if ( thing.className ) {
				rtn = thing.className.split( rexSpace );
			}
		}
		return rtn;
	}  // getClass


	function hasClass ( thing, name ) {
	// A simple check for the existence of one class name.
	// 'thing' param can be an element, element id,
	// or any object that has a className property.
		return !!name && getClass( thing ).indexOf( name ) > -1;
	}  // hasClass


	function setClass ( thing, names, add ) {
	thing = $( thing ) || select( thing );
	// Worker bee for addClass and removeClass.
		var name, i;

		if ( thing && names ) {

			if ( names.split ) {
				// change string of names to array
				names = names.split( rexSpace );
			}

			if ( isArray( names ) ) {

				if ( isArray( thing ) ) {
					// recursive call for each element
					i = thing.length;
					while ( i-- ) {
						setClass( thing[ i ], names, add );
					}
				}

				else {  // one element

					for ( i = 0; i < names.length; i++ ) {  // in order (assuming classList cooperates)
						if ( ( name = names[ i ] ) ) {

							if ( thing.classList ) {
								thing.classList[ add ? 'add' : 'remove' ]( name );
							}

							else if ( add ) {
								if ( !hasClass( thing, name ) ) {
									thing.className = trim( ( thing.className || '' ) + ' ' + name );
								}
							}

							else if ( thing.className ) {  // remove
								thing.className = trim( patch( thing.className,
									rexSpace, '  ',  // handles adjacent duplicates
									RegExp( '(^| )(' + name + ')( |$)', 'g' ), '$1$3'
								) );
							}
						}
					}
				}
			}
		}
	}  // setClass

	function addClass ( thing, names ) {
	// Add class name(s) to element(s) (or other objects).
	// 'names' can be a space-delimited string or array of strings.
		setClass( thing, names, true );
	}  // addClass

	function removeClass ( thing, names ) {
	// Remove class name(s) from element(s) (or other objects).
	// 'names' can be a space-delimited string or array of strings.
		setClass( thing, names, false );
	}  // setClass


	function attr ( $el, name, val ) {
	$el = $( $el ) || select( $el );
	// Get, set or remove one or more attributes from one or more elements.
		var rtn, action, i;

		if ( isArray( $el ) ) {
			for( i = 0; i < $el.length; i++ ) {
				rtn = attr( $el[ i ], name, val );
			}
		}

		else {

			if ( typeOf( name, 'object' ) ) {
				for ( i in name ) {
					attr( $el, i, name[ i ] );
				}
			}

			else if ( name ) {
				action = val === null ? 'remove'
					: val === undef ? 'get'
					: 'set';
				rtn = $el[ action + 'Attribute' ]( name, val );
			}
		}
		return rtn;
	}  // attr


	function activateLinks ( $baseEl, ownerBox ) {
	$baseEl = $( $baseEl );
	// Light up floatboxed elements.
		var $container, links, $link, baseSettings, classNames, containerSettings, href, i, j,
			containers = [];

		function clickHandler ( e ) {
		// minimal click handler while page is loading, updated in core:activateItem
			stopEvent( e );
			fb.start( this );
		}

		if ( ( baseSettings = settings.baseSettings ) ) {  // init has run

			if ( !getTagName( $baseEl ) ) {  // full (re)activation
				$baseEl = document.body;
				items.length = 0;
			}

			// look for parent containers with the floatbox class and propogate classes and settings
			// to child <a> and <area> elements

			if ( hasClass( $baseEl, floatboxClass ) || baseSettings.activateMedia !== false ) {
				containers = [ $baseEl ];
			}
			containers = containers.concat( select( '.' + floatboxClass, $baseEl ) );

			for ( i = 0; i < containers.length; i++ ) {
				$container = containers[ i ];
				if ( !getTagName( $container, [ 'a', 'area' ] ) ) {

					containerSettings = parseOptions( $container );
					addClass( containerSettings, attr( $container, 'class' ) );  // inherit classOptions
					removeClass( containerSettings, floatboxClass );  // tidyness
					if ( !containerSettings.className ) {
						deleteProp( containerSettings, 'className' );
					}
					classNames = getClass( containerSettings );
					for( j = 0; j < classNames.length; j++ ) {
						containerSettings = extend( {},
							settings.classSettings[ classNames[ j ] ],
							containerSettings
						);
					}

					links = select( [ 'a', 'area' ], $container );
					for ( j = 0; j < links.length; j++ ) {
						$link = links[ j ];
						href = attr( $link, 'href' ) || attr( $link, 'xlink:href' );

						if ( !hasClass( $link, 'nofloatbox' )
							&& !/^mailto:/.test( href )
							&& ( hasClass( $container, floatboxClass ) || parseUrl( href ).fileType )
							// fileType for activateMedia
						) {
							attr( $link, 'data-fb-inherit',
								makeOptionString(
									extend( parseOptions( attr( $link, 'data-fb-inherit' ) ), containerSettings )
								) || null
							);
							addClass( $link, floatboxClass );
						}
					}
				}
			}

			// now that the links are marked, light 'em up
			links = select( 'a.' + floatboxClass + ',area.' + floatboxClass, $baseEl );
			for ( i = 0; i < links.length; i++ ) {
				$link = links[ i ];  // in page order

				if ( !items[ $link.fbx ] ) {  // don't reactivate existing on user call
					data.activateItem( $link, ownerBox );  // will defer until core is loaded
					if ( !$link.onclick ) {
						addEvent( $link, 'onclick', clickHandler );
					}
				}
			}
		}
	}  // activateLinks


	function codeHTML ( str, decode ) {
	// Worker bee for encodeHTML and decodeHTML
		var i,
			rtn = str + '',  // in case someone passes in an object reference
			entities = [
				'&', '&amp;',  // &amp; first when encoding, last when decoding
				'"', '&quot;',
				'>', '&gt;',
				'<', '&lt;'
			];

		if ( decode ) {
			entities.reverse();
		}
		for ( i = 0; i < entities.length; i += 2 ) {
			rtn = patch( rtn, RegExp( entities[ i ], 'g' ), entities[ i + 1 ] );
		}
		return rtn;
	}  // codeHTML

	function decodeHTML ( str ) {
	// Un-encode HTML entities in a string.
		return codeHTML( str, true );
	}  // decodeHTML

	function encodeHTML ( str, doubled ) {
	// Encode HTML entities in a string.
		str = codeHTML ( decodeHTML( str ) );  // decode first to avoid unwanted double-encoding
		if ( doubled ) {
			str = codeHTML( str );
		}
		return str;
	}  // encodeHTML


	function serialize ( source ) {
	source = document.forms[ source ] || $( source );
	// Returns a form's fields or an object's members as a properly encoded GET string.
	// 'source' may be a form node, name or id, or an object.
		var prop, vals, i,
			pairs = [];

		if ( getTagName( source, 'form' ) ) {
			source = data.fbIsReady ? fb.getFormValues( source ) : {};  // getFormValues is in core.js
		}

		if ( typeOf( source, 'object' ) ) {

			for ( prop in source ) {
				vals = source[ prop ];
				if ( !isArray( vals ) ) {
					vals = [ vals ];  // accept arrays or singletons but process as array
				}

				for ( i = 0; i < vals.length; i++ ) {
					pairs.push( encode( prop ) + '=' + encode(
						vals[ i ] !== undef
						? patch( ''+vals[ i ], /\r?\n/g, '\r\n' )  // newlines are encoded CRLF
						: ''
					) );
				}
			}
		}

		return patch( pairs.join( '&' ), /%20/g, '+' );  // the spec says space becomes +
	}  // serialize


	function deserialize ( str ) {
	str = ''+str === str ? str : '';
	// Return an object from a serialized string.
	// Duplicate values are returned in an array.
		var pair, i,
			rtn = {},
			pairs = patch( decodeHTML( str ),
				/^\?/, '',
				/\+/g, '%20',
				/%25/g, '%'
			).split( '&' );

		for ( i = 0; i < pairs.length; i++ ) {
			pair = /([^=]*)=?(.*)/.exec( pairs[ i ] );
			multiAssign( rtn, decode( pair[ 1 ] ), decode( pair[ 2 ] ) );
		}
		return rtn;
	}  // deserialize


	function setInnerHTML ( $el, html ) {
		try {  // illegal element (e.g., img) or bad html might be passed
			$( $el ).innerHTML = html || '';
		}
		catch ( _ ) { }
	}  // setInnerHTML

///  end api functions

///  begin shared-on-data functions

	function setTimer ( func, delay, key, locker ) {
	locker = locker || timeouts;
	// Extended setTimeout wrapper

		clearTimer( key, locker );  // cancel pending
		locker[ key ] = setTimeout(  // set new
			function () {
				clearTimer( key, locker );  // flag completion
				trigger( func );
			},
			delay || 13  // 13 msecs for timer animations ( 60MHz screen refresh is 16.7 msecs )
		);
	}  // setTimer


	function clearTimer ( key, locker ) {
	// clearTimeout wrapper companion to setTimer

		if ( key ) {
			locker = locker || timeouts;
			clearTimeout( locker[ key ] );
			deleteProp( locker, key );
		}
	}  // clearTimer


	function trigger ( func, param ) {
	func = self[ func ] || func;
	// Executes fb's various callbacks and timers in various formats.
		var rtn,
			type = typeOf( func );

		if ( func ) {
			rtn =
				type == 'function' ? func.apply( self, isArray( param ) ? param : [ param ] )
				: isArray( func ) ? trigger( func.shift(), func )  // [ function, arg1, arg2, ... ]
				: type == 'string' && require( undef, func );
		}

		return rtn;
	}  // trigger


	function parseUrl ( url, addParams ) {
	url = newElement( 'div', '<a href="' + encodeHTML( url ) + '"></a>' ).firstChild.href;  // normalized to full url
	addParams = addParams || {};
	// Normalizes a path and returns an object of path parts.
	// 'addParams' is object of things to add to the query string.
		var prop, addQuery,
			match = /([^?#]*)(\??[^#]*)(#?.*)/.exec( url ),
			path = match[ 1 ],
			query = match[ 2 ],
			hash = match[ 3 ],
			parts = path.split( '/' ),
			name = parts[ parts.length - 1 ].split( '.' ),
			ext = ( name.length > 1 && name.pop() || '' ).toLowerCase(),  // no dot
			empties = /\/\/\//.test( url ) ? 2 : 1,  // one more empty part if file protocol
			domain = parts[ empties + 1 ] || '',  // server dns name
			objQuery = deserialize( query ),
			rtn = {
				host: parts.slice( 0, empties + 2 ).join( '/' ),  // https?://domain or file:///C:
				baseUrl: parts.slice( 0, -1 ).join( '/' ) + '/',  // full folder path (aka base href)
				fileName: name.join( '.' ),  // extensionless filename
				hash: hash,  // includes '#'
				noQuery: path  // no query no hash
			};

		// extend querystring with addParams
		for ( prop in objQuery ) {
			deleteProp( addParams, prop );  // querystring params from the original url take precedence
		}
		if ( ( addQuery = serialize( addParams ) ) ) {
			query += ( query.length ? '&' : '?' ) + addQuery;  // retain original string (e.g., empty params)
		}

		path += query;  // will retain bare '?' from original url
		extend( rtn, {
			noHash: path,  // no hash, but includes querystring with any added params
			fullUrl: path + hash,  // full original URL with added query params
			query: deserialize( query ),
			fileType: /jpe?g|png|gif|webp/.test( ext ) ? 'image'
				: ext == 'mp4' || /\b(youtu\.?be|vimeo)\b/i.test( domain ) ? 'video'
				: ext == 'pdf' ? 'pdf'
				: ''  // will default to iframe, activateMedia needs this to be falsey
		} );
		return rtn;
	}  // parseUrl


	function parseOptions ( source ) {
	// Return object of name:value pairs from a query string or data-fb-options attribute.
	// e.g., "showClose:false navType:none"    ( typical data-fb-options attribute )
	//   or, "showClose=false&navType=none"    ( queryString syntax )
	//   or, "showClose:false; navType:none;"  ( css style syntax )
	//   or, "showClose:false, navType:none"   ( javascript object syntax )
		var pairs, pair, match, prop, i,
			rtn = {},
			sourceType = typeOf( source ),
			quotes = [],
			rexBackquote = /(`|~)([\s\S]*?)\1/g,  // for handling back/tilde-quoted strings
			marker = '```',
			map = {  // for parseValue
				"true": true,
				"false": false,
				"null": null,
				// the following are all legacy
				"default": undef,
				"auto": undef,
				"yes": true,
				"no": false,
				"max": '100%'
			};

		function parseValue ( val ) {
		// destringify something in an options-appropriate way
			var number = +patch( val, /px$/, '' );

			return (
				val in map ? map[ val ]
				: number != number ? val  // NaN
				: number
			);
		}

		if ( sourceType == 'string' ) {
			source = decodeHTML( source );

			// capture backquoted segments from the string
			while ( ( match = rexBackquote.exec( source ) ) ) {
				quotes.push( match[ 2 ] );  // capture all backquoted segments
			}

			// remove backquoted segments (leaving a marker)
			// and standardize string to "key1:value1 key2:value2" pairs
			source = trim( patch( source,
				rexBackquote, marker,
				/\s*[:=]\s*/g, ':',  // = to :  trim surrounding spaces
				/[;&,]/g, ' '  // & ; , to space
			) );

			// parse individual pairs into rtn object members
			pairs = source.split( rexSpace );
			i = pairs.length;
			while ( i-- ) {  // backwards for quotes popping
				pair = pairs[ i ].split( ':' );
				// in case of dups, first one listed will take precedence

				if ( pair[ 0 ] && pair[ 1 ] ) {
					rtn[ pair[ 0 ] ] = pair[ 1 ] == marker
						? quotes.pop() || ''  // put back-quoted strings back in place
						: parseValue( patch( pair[ 1 ],
							/^(['"])(.+)\1$/, '$2'  // don't quote me
						) );
				}
			}
		}

		else if ( sourceType == 'object' ) {
			for ( prop in source ) {
				rtn[ prop ] = parseValue( source[ prop ] );
			}
		}

		else if ( sourceType == 'node' ) {
			extend( rtn,
				parseOptions( attr( source, 'data-fb-inherit' ) ),
				parseOptions( attr( source, 'rev' ) ),  // legacy
				parseOptions( attr( source, 'data-fb-options' ) )
			);
		}

		// include mobile overrides for smallScreen devices
		pairs = smallScreen && rtn.mobile;
		if ( pairs ) {
			extend( rtn, parseOptions( pairs ) );
		}
		deleteProp( rtn, 'mobile' );  // they've been merged

		return rtn;
	}  // parseOptions


	function makeOptionString ( settings ) {
	// Generate a string of options from an object of settings.
		var prop, val, delim,
			rtn = '';

		for ( prop in settings ) {
			val = settings[ prop ];
			if ( typeOf( val, 'object' ) ) {
				val = makeOptionString( val );
			}
			if ( /[:=&;,\s]/.test( val ) ) {
				delim = /`/.test( val ) ? '~' : '`';
				val = delim + val + delim;  // backquotes if val contains delimiters
			}
			rtn += prop + ':' + val + ';';
		}

		return encodeHTML( rtn );
	}  // makeOptionString


	function getOwnerView ( $node ) {
	// Returns the window object a node resides in.
	// Undefined if not a node, is unattached,
	// or if the window is cross-domain or tombstoned.

		try {
			// first get the document object
			$node = $node.document  // from a window object
				|| ( $node.documentElement || $node ).ownerDocument;  // from a document or element
			// then get the document's window object
			return $node.defaultView;
		}
		catch ( _ ) {}
	}  // getOwnerView


	function newElement ( type, content ) {
	// Extended createElement wrapper.
		var $el = document.createElement( type );

		if ( type == 'a' ) {
			addClass( $el, 'nofloatbox' );  // so box controls can't activate by inheritance
		}
		if ( content ) {
			setInnerHTML( $el, content );
		}

		return $el;
	}  // newElement


	function placeElement ( $el, $to, $before ) {
	// Attach or detach a child element.

		if ( $el ) {

			if ( $to ) {
				try {
					if ( $el.ownerDocument != $to.ownerDocument ) {
						$to.ownerDocument.adoptNode( $el );
					}
					return $to.insertBefore( $el, $before || null );
					// same as appendChild when $before is null
				}
				catch ( _ ) { }
			}

			else if ( $el.parentElement ) {
				$el.parentElement.removeChild( $el );
			}
		}
	}  // placeElement


	function getTagName ( $el, filter ) {
	// Element's lowerCase tagName string.
		var tagName = ( $el && $el.tagName || '' ).toLowerCase();

		if ( filter ) {
			if ( ( isArray( filter ) ? filter : [ filter ] ).indexOf( tagName ) < 0 ) {
				tagName = '';
			}
		}

		return tagName;
	}  // getTagName


	function patch () {
	// Chain string.replace via multiple arguments.
		var args = [].slice.call( arguments ),
			rtn = args.shift(),
			i = 0;

		if ( ''+rtn === rtn ) {
			for ( ; i < args.length; i += 2 ) {
				rtn = rtn.replace( args[ i ], args[ i + 1 ] );
			}
		}
		return rtn;
	}  // patch


	function multiAssign ( obj, prop, val ) {
	// Assigns an array for multiple values to an object.property.

		if ( prop in obj ) {  // already exists so we need an array
			if ( !isArray( obj[ prop ] ) ) {
				obj[ prop ] = [ obj[ prop ] ];  // existing singleton becomes first member of a new array
			}
			obj[ prop ].push( val );  // add new value to the array
		}

		else if ( prop ) {
			obj[ prop ] = val;  // assign a solo scalar, no array
		}
	}  // multiAssign


	function codeURIComponent ( str, encode ) {
	// Worker bee for encode and decode function.
	// Wraps en/decodeURIComponent and prevents error throws.

		try {
			str = self[ ( encode ? 'en' : 'de' ) + 'codeURIComponent' ]( str );
		}
		catch ( _ ) { }
		return str;
	}  // codeURIComponent

	function encode ( str ) {
	// Wrapper for encodeURIComponent.
		return codeURIComponent( str, true );
	}  // encode

	function decode ( str ) {
	// Wrapper for decodeURIComponent.
		return codeURIComponent( str, false );
	}  // decode


	function deleteProp ( obj, prop ) {
		delete ( obj || {} )[ prop ];
	}  // deleteProp


	function trim ( str ) {
	// Standard trim plus collapse of internal whitespace to a single space character.
		return patch( str.trim(), rexSpace, ' ' );
	}  // trim

///  end shared-on-data functions

///  begin internal functions

	function defer ( name, obj ) {
	// Provides early access to api functions defined in core.js
	// by queueing the requests for ready.

		return function ( a, b, c ) {
			ready( function () {
				( obj || fb )[ name ]( a, b, c );
			}, true );
		};
	}  // defer


	function init () {
	// DOM is ready, now get fb ready.
		var	siteOptions = fb.fbOptions || {},
			pageOptions = self.fbOptions || {},
			baseSettings = extend(
				parseOptions( siteOptions.global ),
				parseOptions( self.fbPageOptions ),  // legacy fbPageOptions
				parseOptions( pageOptions.global ),
				smallScreen && parseOptions( siteOptions.mobile ),
				smallScreen && parseOptions( pageOptions.mobile )
			),
			typeSettings = expand( extend(
				parseOptions( siteOptions.type ),
				parseOptions( self.fbTypeOptions ),  // legacy fbTypeOptions
				parseOptions( pageOptions.type )
			) ),
			classSettings = expand( extend(
				parseOptions( siteOptions.className ),
				parseOptions( self.fbClassOptions ),  // legacy fbClassOptions
				parseOptions( pageOptions.className )
			) );

		function expand ( obj ) {
		// parse out settings from individual type and className options
			var prop,
				rtn = {};

			for ( prop in obj ) {
				rtn[ prop ] = parseOptions( obj[ prop ] );
			}
			return rtn;
		}

		if ( baseSettings.autoGallery !== false ) {
			typeSettings.image = extend( {}, typeSettings.image, { group: 'autoGallery' } );
		}

		// capture categories of option settings
		extend( settings, {
			baseSettings: baseSettings,
			typeSettings: typeSettings,
			classSettings: classSettings
		} );

		// allow pointer-events now, and don't let every link inherit from this loading indicator
		removeClass( document.documentElement, floatboxClass );
		floatboxClass = baseSettings.floatboxClass || floatboxClass;
		activateLinks();  // get the click handler in place

		// get (if not already got) and initalize core
		require(
			!data.coreInit && patch( scriptUrl.fullUrl,
				scriptUrl.baseUrl + scriptUrl.fileName,
				scriptUrl.baseUrl + 'core'
			),
			function() {  // resolve .coreInit at callback runtime
				data.coreInit();
			}
		);
	}  // init

///  end internal functions

///  execute

	// floatbox.css will disable mouse actions while page is loading
	// the class is removed by init
	addClass( document.documentElement, floatboxClass );

	// expose the api
	self.fb = extend( fb, {  // create or reassign the fb global
		data: data,

		// API props & funcs
		version: version,
		build: build,
		path: fbPath,
		docReady: docReady,
		ready: ready,
		$: $,
		select: select,
		require: require,
		extend: extend,
		addEvent: addEvent,
		removeEvent: removeEvent,
		stopEvent: stopEvent,
		serialize: serialize,
		deserialize: deserialize,
		getClass: getClass,
		hasClass: hasClass,
		addClass: addClass,
		removeClass: removeClass,
		attr: attr,
		typeOf: typeOf,
		encodeHTML: encodeHTML,
		decodeHTML: decodeHTML,
		smallScreen: smallScreen,  // expose the boolean result, not the function

		// available at load time, but delivered by core when ready (can't use return values)
		start: defer( 'start' ),
		ajax: defer( 'ajax' ),
		animate: defer( 'animate' ),
		preload: defer( 'preload' ),

		// legacy stuff
		setInnerHTML: setInnerHTML,
		DOMReady: docReady,
		executeJS: require,
		getByTag: select
	} );

	// expose shared functions and vars for use by core
	extend( data, {

		// functions
		activateItem: defer( 'activateItem', data ),
		activateLinks: activateLinks,
		setTimer: setTimer,
		clearTimer: clearTimer,
		trigger: trigger,
		parseUrl: parseUrl,
		parseOptions: parseOptions,
		makeOptionString: makeOptionString,
		getOwnerView: getOwnerView,
		newElement: newElement,
		placeElement: placeElement,
		getTagName: getTagName,
		patch: patch,
		multiAssign: multiAssign,
		deleteProp: deleteProp,

	// vars shared on data across all connected frames
		instances: [],  // active floatboxes
		items: items,  // activated links, etc
		popups: [],  // fbPop*s
		preloads: {},  // fb.preload keeps its results here
		wrappers: {},  // hidden divs that stuff comes from
		offHints: {},  // hints (browser tooltips) that are off because they've been seen

	// per-frame vars
		settings: settings,
		timeouts: timeouts,
		locationUrl: locationUrl,
		scriptUrl: scriptUrl.fullUrl,
		fbParent: parentView
	} );

	docReady( [ removeEvent, addEvent( document, 'DOMContentLoaded', docReady ) ] );

	setTimer( function () {
	// The new timer thread allows fbOptions.js to be appended to floatbox.js.

		require(
			!fb.fbOptions && patch( scriptUrl.fullUrl,
				scriptUrl.baseUrl + scriptUrl.fileName,
				scriptUrl.baseUrl + 'fbOptions'
			),
			[ docReady, init ]  // run init regardless of whether fbOptions.js fetched or not
		);
	} );

} )();
