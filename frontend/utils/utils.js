/**
 * Logos are stored as {display_name}-{nickname} 
 * based on our teams API. 
 *
 * Convert the human readable to file formatted version 
 * to retrieve the proper logo for each helmet.
 * 
 * @param {string} name
 * @returns {string} id
 */
export function nameToLogoImage(name) {
    return name.replace(/\s+/g, '-').toLowerCase()
}

/**
 * Filter array items based on search criteria (query)
 */
export function filterItems(arr, query) {
    return arr.filter(function(el) {
        return el.toLowerCase().indexOf(query.toLowerCase()) !== -1
    })
  }

/**
 * 
 * @author JBE
 * 
 * make sure undefined, goes away. for now.
 * 
 */
export function getSafe(fn, defaultVal) {
    try {
        return fn();
    } catch (e) {
        return defaultVal;
    }
}

export function setSlugFromTeam( displayNickName ){

    return displayNickName.replace( ' ', '-').toLowerCase();

}

export function searchBySlug( path ){

    var slugs = path.split("/");
    //get the last item from the slug
    var teamSlug = setSlugFromTeam( slugs[2] );
    var teamName = teamSlug.split("-");
    
    return teamName[ teamName.length - 1 ];


} 

export function reverseTeamName(path){

    let seoSplitter = path.split('/');
    var teamSlug = setSlugFromTeam( seoSplitter[2] ).replace( "-", " " ).replace(/(^|\s)\S/g, l => l.toUpperCase());
    return teamSlug;


}

export function reverseSlugName(slug){

    var teamSlug = setSlugFromTeam( slug ).replace( "-", " " ).replace(/(^|\s)\S/g, l => l.toUpperCase());
    return teamSlug;


}

export default { nameToLogoImage, filterItems, getSafe, searchBySlug, reverseTeamName, reverseSlugName}