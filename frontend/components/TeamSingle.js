import React from 'react';
import { nameToLogoImage, filterItems, getSafe, splitSlug } from "../utils/utils.js";
import Config from '../config';
import teamColors from '../static/teams/team-colors.json'
import Link from 'next/link';
import fetch from 'isomorphic-unfetch';
import Router from 'next/router';
import useSWR from 'swr'
import parse from 'html-react-parser'


export const TeamSingle = ( props ) => {

  const teamName                = props.team.display_name + ' ' + props.team.nickname;
  const logoName                = nameToLogoImage( props.team.display_name + ' ' + props.team.nickname );
  const teamUrl                 = props.team.display_name.toLowerCase() + '-' + props.team.nickname.toLowerCase();
  const logoPath                  = '/static/images/nfl/' + logoName + '.svg';
  const content                 = typeof props.content.post !== 'undefined' ? parse( props.content.post.content.rendered ) : 'No content for this team. Keep searching.';
  
  /**
   * 
   * JBE 
   * 
   * Map some colours, classes have been manually created 
   * in /src/styles.css in accordance with the tailwindcss config docs.
   * All unused classes are purged resulting in a very tiny stylesheet.
   * 
   */
  const teamColorArray          = typeof( teamColors.filter( activity => ( activity.name.includes( teamName ) ) ) !== undefined ) ? teamColors.filter( activity => ( activity.name.includes( teamName ) ) ) : false;
  let primaryTeamColorClass = getSafe(() => '_' + teamColorArray[0].colors.hex[0] );  
  if ( primaryTeamColorClass == undefined ){
    primaryTeamColorClass = '_default_bg';
  }
  

  /**
   * 
   * @author JBE
   * 
   * useSWR is something I'm interested in, so, 
   * thought I'd give it a shot.
   * 
   * It doesn't play nicely with multiple requests but, 
   * is key in the framer-motion goal I was hoping to show.
   * 
   * @todo resolve react-router-dom errors to enable Royter Syntax
   * 
  **/
  const fetcher = (...args) => fetch(...args).then((res) => res.json())
  const { data, error } = useSWR( Config.PROXIED_API_BASE + 'rankings?api_key=' + Config.API_TOKEN, fetcher );
 


  //Wait a sec.
  if (error) return ''
  if (!data) return ''


  /** 
   * 
   * @author JBE
   * ranks?
   * 
   * @todo Apply sort filters on conference, rank, alphbetical
   * 
  */
  const rankings = data.results.data;
  var teamRank = rankings.filter(rankings => rankings.team_id == props.team.id  );
  var teamRankValue = getSafe(() => teamRank[0].rank ) == '' ? 'n/a' : getSafe(() => teamRank[0].rank );
  /**
   * 
   * @author JBE
   * 
   * @todo Make sure that files exist, redundanncy, etc,
   * and set defaults on error.
   */
  return (
<>
<div class="flex flex-col md:flex-row overflow-hidden bg-white rounded-lg shadow-xl  mt-4 w-100 mx-2">
  
  <div class="h-64 w-auto md:w-1/2">
    
    <div className={"relative w-1/3 h-full w-full " + primaryTeamColorClass }>

    
      <img className="absolute w-5/12 top-2 left-2 z-10" src={logoPath} alt={teamName} title={teamName} />
      <img className="acme-flip-horizontal absolute z-0" src="/static/images/helmet_colors.svg" alt="" />  
    </div>
    
    </div> <div class="w-full py-4 px-6 text-gray-800 flex flex-col justify-between">
      <h1 className="text-8xl font-medium text-gray-900">#{teamRankValue}</h1>
      <h2 className="text-6xl font-medium text-gray-900" title={teamName}>{teamName}</h2>
      
      <p class="mt-2">
      {content}
      </p>
      
      </div>
          
          </div>
  
  
  </>
  )

};

export default TeamSingle;