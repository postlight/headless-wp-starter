import React from 'react';
// import { StyleSheet, Text, View } from "react-native";
import nameToLogoImage from "../utils/utils.js";
import teamColors from '../static/teams/team-colors.json'
import Link from 'next/link';
import fetch from 'isomorphic-unfetch';
import useSWR from 'swr'
import Config from '../config.js';


/**
 * 
 * @author JBE
 * 
 * make sure undefined, goes away. for now.
 * 
 */
function getSafe(fn, defaultVal) {
  try {
      return fn();
  } catch (e) {
      return defaultVal;
  }
  }

export const TeamCard = ( props ) => {
  
/**
 * 
 * @author JBE
 * 
 * @issue react router dom module breaks dynamic routing
 * 
 * @description Just take the team data, spit it out, make some color changes based on 
 * the manually generated primary teams colors ( see teams.json )
 * 
 * 
 */
  

/**
   * 
   * @author JBE 
   * 
   * @description Map some colours, classes have been manually created 
   * in /src/styles.css in accordance with the tailwindcss config docs.
   * All unused classes are purged resulting in a very tiny stylesheet.
   * 
   * 
   */
  const teamName    = props.team.display_name + ' ' + props.team.nickname;
  const logoName    = nameToLogoImage( props.team.display_name + ' ' + props.team.nickname );
  const teamUrl = props.team.display_name.toLowerCase() + '-' + props.team.nickname.toLowerCase();
  let logoPath    = '/static/images/nfl/' + logoName + '.svg';
  const teamColorArray  = typeof( teamColors.filter( activity => ( activity.name.includes( teamName ) ) ) !== undefined ) ? teamColors.filter( activity => ( activity.name.includes( teamName ) ) ) : false;
  let primaryTeamColorClass = getSafe(() => '_' + teamColorArray[0].colors.hex[0] );
  if ( primaryTeamColorClass == undefined ){
    primaryTeamColorClass = '_default_bg';
  }

  const fetcher = (...args) => fetch(...args).then((res) => res.json())
  const { data, error } = useSWR( Config.PROXIED_API_BASE + 'rankings?api_key=' + Config.API_TOKEN, fetcher );
 
  if (error) return ''
  if (!data) return ''
  
  const rankings = data.results.data;

  var teamRank = rankings.filter(rankings => rankings.team_id == props.team.id  );

  var teamRankValue = getSafe(() => teamRank[0].rank ) == '' ? 'n/a' : getSafe(() => teamRank[0].rank );

  return (
              
<Link as={`/teams/team/${teamUrl}`}
      href={`/teams/team/?slug=${teamUrl}&apiRoute=post`}
 >
  <div className={"flex items-center m-3 w-full h-full drop-shadow " + primaryTeamColorClass }>
    <div className="ml-2 relative w-1/3 top-2 h-24 w-24 drop-shadow">
      <img className="absolute w-5/12 top-1 left-1.5 z-10 rounded-full" src={logoPath} alt={teamName} title={teamName} />
      <img className="drop-shadow-lg acme-flip-horizontal absolute z-0 rounded-full" src="/static/images/helmet_colors.svg" alt="" />
      
    </div>
    <div className="ml-4 relative w-2/3 h-full bg-white p-5">
    <div className="text-4xl font-medium text-gray-900">#{teamRankValue}</div>
      <div className="text-sm font-medium text-gray-900" title={teamName}>{teamName}</div>
    </div>
  </div>
  </Link>
  )

};

export default TeamCard;