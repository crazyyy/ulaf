var Innocow = Innocow || {};
Innocow.WP = Innocow.WP || {};

Innocow.WP.Html = class Html {

    constructor( container ) {

        container = container || document;

        this.classNames = {
            statusContainer: "status",
        }

        this.cnContainerSubmitStatus = "status";

    }

    _setDataAttributeByFieldName( attr, value, name, container=this.container, sendEvent=false ) {

        let elements;
        
        if ( container.elements ) {
            elements = container.elements;
        } else {
            elements = container.querySelectorAll( "[name]" );
        }

        Array.from( elements ).map( element => { 

            if ( element.name === name ) {

                element.setAttribute( "data-"+attr, value );

                if ( sendEvent ) {
                    element.focus();
                    element.dispatchEvent( new Event( "change", { "bubbles": true } ) );
                }

            }

        } );

    }    

    _setPropertyByFieldName( property, value, name, container=this.container, sendEvent=false ) {

        let elements;
        
        if ( container.elements ) {
            elements = container.elements;
        } else {
            elements = container.querySelectorAll( "[name]" );            
        }

        Array.from( elements ).map( element => { 

            if ( element.name === name ) {

                element[property] = value;

                if ( sendEvent ) {
                    element.focus();
                    element.dispatchEvent( new Event( "change", { "bubbles": true } ) );
                }

            }

        } );

    }

    _setPropertyByClassName( property, value, cn, container=this.container, sendEvent=false ) {

        let elements = container.getElementsByClassName( cn );

        Array.from( elements ).map( element => { 

            element[property] = value;

            if ( sendEvent ) {
                element.focus();
                element.dispatchEvent( new Event( "change", { "bubbles": true } ) );
            }

        } );

    }

    _setStyleByClassName( css, value, cn, container=this.container, sendEvent=false ) {

        let elements = container.getElementsByClassName( cn );

        Array.from( elements ).map( element => { 

            element.style[css] = value;

            if ( sendEvent ) {
                element.focus();
                element.dispatchEvent( new Event( "change", { "bubbles": true } ) );
            }

        } );

    }    

    _appendChildByClassName( child, cn, container=this.container, sendEvent=false ) {

        let elements = container.getElementsByClassName( cn );

        Array.from( elements ).map( element => { 

            element.append( child.cloneNode( true ) );

            if ( sendEvent ) {
                element.focus();
                element.dispatchEvent( new Event( "change", { "bubbles": true } ) );
            }

        } );

    }

    _appendChildByFieldName( child, name, container=this.container, sendEvent=false ) {

        let elements;
        
        if ( container.elements ) {
            elements = container.elements;
        } else {
            elements = container.querySelectorAll( "[name]" );
        }        

        Array.from( elements ).map( element => { 

            if ( element.name === name ) {

                element.append( child.cloneNode( true ) );

                if ( sendEvent ) {
                    element.focus();
                    element.dispatchEvent( new Event( "change", { "bubbles": true } ) );
                }

            }

        } );

    }

    createNotice( message, cnStatus ) {

        let x = '<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false"><path d="M13 11.9l3.3-3.4-1.1-1-3.2 3.3-3.2-3.3-1.1 1 3.3 3.4-3.5 3.6 1 1L12 13l3.5 3.5 1-1z"></path></svg>';
        let noticeId = Math.random().toString(36).slice(2);

        let notice = document.createElement( "div" );
        let noticeContent = document.createElement( "p" );
        let buttonClose = document.createElement( "span" );
        let msg = document.createElement( "span" );
        let link = document.createElement( "a" );

        notice.id = noticeId;
        notice.classList.add( ...[ "notice", cnStatus ] );
        notice.append( noticeContent );

        noticeContent.append( msg );
        noticeContent.append( buttonClose );

        msg.innerHTML = message;

        buttonClose.append( link );
        buttonClose.style.fontWeight = "900";
        buttonClose.style.float = "right";

        link.innerHTML = x;
        link.href = "#";
        link.addEventListener( "click", eventClick => {
            let container = document.getElementById( noticeId ).parentElement;
            container.removeChild( document.getElementById( noticeId ) );
        } );

        return notice;

    }

    displayStatus( message, isError=false, cnContainer ) {

        let containers = document.getElementsByClassName( cnContainer );
        let cnStatus = isError ? "error" : "success";

        for ( let container of containers ) {

            container.style.display = "block";
            container.append( this.createNotice( message, cnStatus) );

        }

        if ( containers.length >= 1 ) {

            if ( window.pageYOffset > containers[0].getBoundingClientRect().top ) {
                window.scrollTo( {
                    top: containers[0].getBoundingClientRect().top,
                    behavior: "smooth"
                } );
            }

        }

    }

    displayErrorStatus( message, cnContainer=this.classNames.statusContainer ) {
        this.displayStatus( message, true, cnContainer );
    }

    displayOkStatus( message, cnContainer=this.classNames.statusContainer ) {
        this.displayStatus( message, false, cnContainer );   
    }

}
