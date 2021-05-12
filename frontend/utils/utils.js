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
export default function nameToLogoImage(name) {
    return name.replace(/\s+/g, '-').toLowerCase()
}

/**
 * Filter array items based on search criteria (query)
 */
function filterItems(arr, query) {
    return arr.filter(function(el) {
        return el.toLowerCase().indexOf(query.toLowerCase()) !== -1
    })
  }

function getSafe(fn, defaultVal) {
try {
    return fn();
} catch (e) {
    return defaultVal;
}
}