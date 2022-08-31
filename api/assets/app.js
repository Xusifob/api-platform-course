import {EventSourcePolyfill} from 'event-source-polyfill';

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

const data = {
  "email": "customer1@api-platform-course.com",
  "password": "myAwesomePassword"
};
const init = async () => {


  const response = await fetch('/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  });
  const tokens = await response.json(); // parses JSON response into native JavaScript

  const userResponse = await fetch('/users/me', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      "Authorization": `Bearer ${tokens.token}`
    },
  });


  const user = await userResponse.json(); // parses JSON response into native JavaScript


  const topic = `/users/${user.id}/notification`;
  const token = tokens.mercure_token;

  const source = new EventSourcePolyfill(`https://localhost/.well-known/mercure?topic=${topic}`, {
    headers: {
      'Authorization': `Bearer ${token}`,
    }
  });

  source.onmessage = e => {
    const data = JSON.parse(e.data);
    console.log(data);
  }
}

init();
