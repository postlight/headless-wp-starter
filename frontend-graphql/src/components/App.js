import React, { Component } from 'react';
import Header from './Header';
import Footer from './Footer';
import { Switch, Route } from 'react-router-dom';
import Home from './Home';
import Login from './Login';
import Search from './Search';
import Page from './Page';
import Post from './Post';
import Category from './Category';

class App extends Component {
    render() {
        return (
            <div className="center">
                <Header />
                <div className="pa1 padding table">
                    <Switch>
                        <Route exact path="/" component={Home} />
                        <Route exact path="/login" component={Login} />
                        <Route exact path="/search" component={Search} />
                        <Route exact path="/page/:slug" component={Page} />
                        <Route exact path="/post/:slug" component={Post} />
                        <Route
                            exact
                            path="/category/:slug"
                            component={Category}
                        />
                    </Switch>
                </div>
                <Footer />
            </div>
        );
    }
}

export default App;
