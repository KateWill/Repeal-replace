/**
 * Created by bogdan on 7/13/16.
 */

function append_element(options) {
    if(options == undefined)
        return false;
    if(!("elementType" in options))
        return false;

    var parent = null;

    if(!!options.following)
        parent = options.following.parentElement;
    else if(!!options.inside)
        parent = options.inside;
    else if(!!options.replacing)
        parent = options.replacing.parentElement;
    else if(options.elementType == "script")
        parent = document.head;
    else
        parent = document.body;

    if(parent == null)
        return false;

    var element = document.createElement(options.elementType);
    delete options.elementType;

    element = jQuery.extend(element, options);

    if(!!options.replacing)
        parent.replaceChild(element, options.replacing);
    else
        parent.appendChild(element);
}