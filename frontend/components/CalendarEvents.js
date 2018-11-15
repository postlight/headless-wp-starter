import React from 'react'
import parse from 'date-fns/parse'
import compareAsc from 'date-fns/compare_asc'
import isBefore from 'date-fns/is_before'

const CalendarEvents = ({events}) => {
  const now = new Date()
  const past_events = events.filter(({sort_date}) => isBefore(sort_date, now)).sort(compareAsc)
  const upcoming_events = events.filter(({sort_date}) => !isBefore(sort_date, now)).sort(compareAsc)
  const labels = ['Upcoming Events', 'Past Events']
  return (
    <div id="calendar-events">
      {[upcoming_events, past_events].map((events, i) =>
        <React.Fragment key={i}>
          <h1>{labels[i]}</h1>
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Location</th>
                <th>Link</th>
              </tr>
            </thead>
            <tbody>
            {events.map(({name, date, location, link}) =>
              <tr key={name}>
                <td>{name}</td>
                <td>{date}</td>
                <td>{location}</td>
                <td>
                  {link &&
                    <a href={link.url} target={link.target} title={link.title}>{link.title}</a>
                  }
                </td>
              </tr>
            )}
            </tbody>
          </table>
        </React.Fragment>
      )}
    </div>
  )
}

export default CalendarEvents;
