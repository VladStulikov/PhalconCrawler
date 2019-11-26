const path = require('path');

module.exports = {
  entry: './app/js-src',
  output: {
    path: path.resolve('./public/js'),
    filename: 'index_bundle.js'
  },
  module: {
    rules: [
      { test: /\.js$/, loader: 'babel-loader', exclude: /node_modules/ }
    ]
  },
  performance: {
	    maxEntrypointSize: 512000,
	    maxAssetSize: 512000
	  }  
}