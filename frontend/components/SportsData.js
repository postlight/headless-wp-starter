import Config from '../config';
import fetch from 'isomorphic-unfetch';
import useSWR from 'swr'
import TeamCard from '../components/TeamCard';
import TeamSingle from '../components/TeamSingle';
import { motion } from 'framer-motion';
import { AnimatePresence } from 'framer-motion';

const fetcher = (...args) => fetch(...args).then((res) => res.json())

function SportsData() {
  
  const { data, error } = useSWR( Config.PROXIED_API_BASE +'api?api_key=' + Config.API_TOKEN, fetcher );
  
  //lets wait until we get something from the spi give us something here
  if (error) return ''
  if (!data) return ''

  
  /**
   * 
   * @author JBE
   * I've decided for the sake of time 
   * that this component will serve two purposes:
   * 
   * 1. Present Teams on the home page
   * 2. Show a Single team and retrieve the wordpress post data ( if it exists ), 
   * for SEO purposes else, Display some basic info about the team from the api call.
   * 
   * If a user visits the home page, show all teams, using the TeamCard ( list item ), component
   * 
   * If a user visits a single team, style it like a featured post, grab the post data,
   * populate the head tags with configurable yoast meta via wordpress dashboard.
   * 
   * 
   **/

  const teams = data.results.data.team;

  if ( location.pathname == '/' ){ 
  
  var singleTeam = false;
  var teamCards = teams.map((team) => (
        
    <TeamCard key={team.nickname} team={team} singleTeam={singleTeam}  />
    
  ))

  } else { 
  
  var singleTeam = true;
  var slugSplit = location.pathname.split("/");
  var urlSlug = slugSplit[3];
  var teamName = urlSlug.split("-");
  var teamSearch = teamName[0];
  
  
  var teamCards = teams.filter(team => team.name.toLowerCase() == teamSearch  ).map(team => (
      
      <TeamSingle key={team.nickname} team={team} singleTeam={singleTeam} />
      
    ));

  }

  return (   

    <div className="grid lg:grid-cols-4 md:grid-cols-2 sm:grid-cols-1 gap-4">

      <AnimatePresence initial={false} exitBeforeEnter> 
        {teamCards}
      </AnimatePresence>

    </div>
          

  );
}

export default SportsData

