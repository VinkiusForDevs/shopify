const express = require('express');
const path = require('path');
const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());
app.use(express.static(path.join(__dirname, 'admin')));

app.get('/', (req, res) => {
  res.send('<h1>Vinkius Connector for Shopify</h1><p>Configure your Shopify app and connect to Vinkius.</p>');
});

app.get('/api/status', (req, res) => {
  res.json({ status: 'ok', connected: false });
});

app.listen(PORT, () => {
  console.log('Vinkius Connector for Shopify running on port ' + PORT);
});