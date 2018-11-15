const CalendarEvents = ({events}) => {
  return (
    <table id="calendar-events">
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
  );  
}

export default CalendarEvents;
