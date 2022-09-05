import Cookies from 'js-cookie';
import 'fetch-ie8';
import 'scorm-again';

const Scorm2004API = window.Scorm2004API;

function setupApi(data) {
  const api = new Scorm2004API({
    lmsCommitUrl: data.commit_url,
    xhrHeaders: {
      'X-XSRF-TOKEN': Cookies.get('XSRF-TOKEN'),
    }
  });

  api.loadFromJSON(data.tracking.cmi);

  window.API_1484_11 = api;
}

function loadIframe(entry) {
  const players = document.getElementsByClassName('scorm-player');
  for(const player of players) {
    player.src = entry;
  }
}

function handleScoData(data) {
  setupApi(data);
  loadIframe(data.entry_url);
}

function loadScoData() {
  fetch(window.scorm_api_data.routes.load, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
    }
  }).then((response) => {
    if(response.status === 200)
      return response.json();
    return {};
  }).then(handleScoData);
}

document.addEventListener('DOMContentLoaded', loadScoData);
