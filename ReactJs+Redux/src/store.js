import { applyMiddleware, createStore } from 'redux' //compose
import { composeWithDevTools } from 'redux-devtools-extension'
import thunkMiddleware from 'redux-thunk'

//import monitorReducersEnhancer from './enhancers/monitorReducers'
//import loggerMiddleware from './middleware/logger'

import { root, preloadedState } from './reducers'

const configureStore = (preloadedState) => {
  const middlewares = [thunkMiddleware]//loggerMiddleware
  const middlewareEnhancer = applyMiddleware(...middlewares)

  const enhancers = [middlewareEnhancer]//monitorReducersEnhancer
  const composedEnhancers = composeWithDevTools(...enhancers)

  const store = createStore(root, preloadedState, composedEnhancers)

  //if (process.env.NODE_ENV !== 'production' && module.hot) {
  //  module.hot.accept('./reducers', () => store.replaceReducer(rootReducer))
  //}

  return store
}

const store = configureStore(preloadedState)

export default store