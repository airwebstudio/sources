import React from 'react'
import ReactDOM from 'react-dom'
import { Route, BrowserRouter as Router, Redirect } from 'react-router-dom'
import { Provider } from 'react-redux'
import store from './store'
import {PrivateRoute as PrivateRouteAuth} from 'react-auth-kit'
import { AuthProvider } from 'react-auth-kit'
import styles from './styles.scss'

import Homepage from './pages/Homepage'
import PageItem from './pages/PageItem'
import Seller from './pages/Seller'

import Dashboard from './pages/Dashboard'
import DashboardSeller from './pages/Dashboard/Seller'
import MeetingAvailabilitiesUpdate from './pages/Dashboard/Seller/MeetingAvailabilitiesUpdate'
import DashboardBuyer from './pages/Dashboard/Buyer'

import Login from './pages/Auth/Login'
import Register from './pages/Auth/Register'
import ForgotPassword from './pages/Auth/ForgotPassword'

import StripeReauth from './pages/Stripe/Reauth'
import StripeReturn from './pages/Stripe/Return'
import Wallet from './pages/Stripe/Wallet'
import Payment from './pages/Stripe/Payment'

import PrivateChatList from './pages/PrivateChat/List'
import PrivateChat from './pages/PrivateChat'
import History from './pages/Chat/History'
import Feeds from './pages/Feeds'
import FeedCreate from './pages/Feeds/FeedCreate'
import ChatHistory from './pages/Chat/History'
import FeedUpdate from './pages/Feeds/FeedUpdate'
import MeetingUpdate from './pages/Dashboard/Seller/MeetingUpdate'
import MeetingInfo from './pages/MeetingInfo'
import MeetingList from './pages/MeetingList'

const PRoute = ({ component: Component, ...rest }) => (
    <PrivateRouteAuth component={Component} loginPath={'/login'} {...rest} />
)

window.getCookie = function(name) {
  var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  if (match) return match[2];
}



const rootElement = document.getElementById('root')
ReactDOM.render(
    <Provider store={store}>
        <AuthProvider authStorageType = {'cookie'}
              authStorageName={'_auth_t'}
              authTimeStorageName={'_auth_time'}
              stateStorageName={'_auth_state'}
              cookieDomain={window.location.hostname}
              cookieSecure={window.location.protocol === "https:"}
              refreshTokenName={'_refresh_t'}
              >
            <Router>
                <Route path="/" exact component={Homepage}></Route>
                <Route path='/page/:page' render={({match}) => {
                            const {url} = match;
                        return <PageItem url={url}/>}}></Route>

                <Route path='/seller/:id' render={({match}) => {
                            const {id} = match.params;
                        return <Seller id={id}/>}}></Route>

                <Route path="/login" exact component={Login}></Route>
                <Route path="/register" exact component={Register}></Route>
                <Route path="/forgot-password" exact component={ForgotPassword}></Route>

                <Route path="/feeds" exact component={Feeds}></Route>

                <PRoute path="/dashboard" exact component={Dashboard}></PRoute>
                <PRoute path="/dashboard/seller" exact component={DashboardSeller}></PRoute>
                <PRoute path="/dashboard/seller/availability/:id" render={({match}) => {
                            const {id} = match.params;
                        return <MeetingAvailabilitiesUpdate id={id}/>}}></PRoute>
                <PRoute path="/dashboard/seller/meeting/:hash" render={({match}) => {
                            const {hash} = match.params;
                        return <MeetingUpdate hash={hash}/>}}></PRoute>
                <PRoute path="/dashboard/buyer" exact component={DashboardBuyer}></PRoute>
                <PRoute path="/dashboard/seller/feeds/create" exact component={FeedCreate}></PRoute>
                <PRoute path="/dashboard/seller/feeds/edit/:id" render={({match}) => {
                            const {id} = match.params;
                        return <FeedUpdate id={id}/>}}></PRoute>

                <PRoute path="/stripe/reauth" component={StripeReauth}></PRoute>
                <PRoute path="/stripe/return" component={StripeReturn}></PRoute>
                <PRoute path="/stripe/wallet" component={Wallet}></PRoute>
                <PRoute path="/stripe/payment" component={Payment}></PRoute>

                <PRoute path="/my-chats" component={PrivateChatList}></PRoute>
                <PRoute path="/chat/:id" exact component={PrivateChat}></PRoute>
                
                <Route path='/chat/history/:roomId' exact render={({match}) => {
                            const {roomId} = match.params;
                        return <ChatHistory roomId={roomId}/>}}></Route>

                <PRoute path="/meeting/:hash" exact component={MeetingInfo}></PRoute>
                <PRoute path="/meetings" exact component={MeetingList}></PRoute>
                
            </Router>
        </AuthProvider>
    </Provider>,
    rootElement
)
