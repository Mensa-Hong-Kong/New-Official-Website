function formatting(date, type = 'date') {
    const options = {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hourCycle: 'h23', // Use 24-hour format
      timeZone: import.meta.env.VITE_APP_TIMEZONE,
    };
  
    // Create a DateTimeFormat object for the desired timezone and options
    const formatter = new Intl.DateTimeFormat('en-US', options);
  
    // Format the date
    const parts = formatter.formatToParts(new Date(date));
  
    // Extract and assemble the parts into the desired format
    const year = parts.find(p => p.type === 'year').value;
    const month = parts.find(p => p.type === 'month').value;
    const day = parts.find(p => p.type === 'day').value;
    const hour = parts.find(p => p.type === 'hour').value;
    const minute = parts.find(p => p.type === 'minute').value;
    const second = parts.find(p => p.type === 'second').value;
  
    let result = `${year}-${month}-${day}`;
    if(type == 'datetime') {
        result += ` ${hour}:${minute}:${second}`;
    } 
    return result;
}

export function formatToDate(date) {
    return formatting(date);
}

export function formatToDatetime(date) {
    return formatting(date, 'datetime');
}