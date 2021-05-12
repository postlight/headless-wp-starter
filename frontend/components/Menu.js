/* eslint-disable jsx-a11y/anchor-is-valid */
import React, { Component } from 'react';
import Link from 'next/link';
import Router from 'next/router';
import Config from '../config';
// import Logo from '../static/images/starter-kit-logo.svg';
// import SearchIcon from '../static/images/search.svg';

const getSlug = url => {
  const parts = url.split('/');
  return parts.length > 2 ? parts[parts.length - 2] : '';
};

class Menu extends Component {
  state = {
    token: null,
    username: null,
  };

  componentDidMount() {
    const token = localStorage.getItem(Config.AUTH_TOKEN);
    const username = localStorage.getItem(Config.USERNAME);
    this.setState({ token, username });
  }

  render() {
    const { menu } = this.props;
    const { token, username } = this.state;

    const handleSelectChange = (e) => {
      location.href = e.target.value;
    }

    return (
      
<div className="h-24 z-50 relative container mx-auto px-6 grid grid-cols-1">
    
    <div className="flex items-center justify-center"><a href="/"
            className="text-white uppercase font-bold text-sm tracking-widest">ACME Sports - 2021 NFL Overview </a></div>
    
</div>










      
      
    );
  }
}
export default Menu;
