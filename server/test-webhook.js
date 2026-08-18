const url = 'https://ankit0909.app.n8n.cloud/webhook-test/8323172d-3a59-45f4-855c-10e35ab7265c';

const payload = {
  "campaignId": "CMP001",
  "campaignName": "AI Calling Marketing Campaign",
  "subject": "Promote our AI-powered calling platform and encourage the lead to book a demo",
  "sender": {
    "name": "Vocallabs",
    "email": "avnishkr.mbd@gmail.com"
  },
  "recipients": [
    {
      "leadId": "L001",
      "firstName": "Avnish",
      "email": "YOUR_REAL_EMAIL@gmail.com",
      "company": "ABC Technologies"
    }
  ]
};

fetch(url, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(payload)
})
  .then(response => response.text())
  .then(data => {
    console.log('Webhook Response:', data);
  })
  .catch(error => {
    console.error('Error sending webhook:', error);
  });
