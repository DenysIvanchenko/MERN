import './App.css';

import React, { useState } from 'react';
import { Route, Routes } from 'react-router-dom';

import Navbar from './pages/components/Navbar';
import Basic from './pages/basic';
import Rota from './pages/rota';
import Total from './pages/total';
import Login from './pages/login';
import NotFound from './pages/404page';
import { createBrowserHistory  } from 'history';

function getToken(){
  const tokenString = sessionStorage.getItem('token');
  if(!tokenString){
    return false;
  }
  const userToken = JSON.parse(tokenString);
  return userToken;
}

function setToken(userToken){
  sessionStorage.setItem('token', JSON.stringify(userToken));
}

function App() {
  const token = getToken();
  const history = createBrowserHistory();
  console.log(token);

  if(!token) {
    history.push('login');
    return <Login />
  }

  return (
    <div className="App">
      <header className="App-header">
        <Navbar />
        <Routes history={history}>
          {
            token && <Route path='/login' element={<Login />} />
          }
          <Route path='/' element={<Basic />} />
          <Route path='basic' element={<Basic />} />
          <Route path='rota' element={<Rota />} />
          <Route path='total' element={<Total />} />
          <Route path="*" element={<NotFound />} />
        </Routes>
      </header>
    </div>
  );
}

export default App;
