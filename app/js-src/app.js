import React, {useState} from 'react';

import {makeStyles} from '@material-ui/core/styles';
import {green, red, blue} from '@material-ui/core/colors';
import {CssBaseline, AppBar,Toolbar,Typography, Button, 
	Paper, Grid, TextField, CircularProgress, Modal, Card, 
	CardContent, Table, TableHead, TableRow, TableCell, TableBody} from '@material-ui/core';

import validator from 'validator';
import axios from 'axios';

const useStyles = makeStyles(theme => ({
	root: {
		flexGrow: 1,
		padding: '30px 30px',
	},
	textField: {
		width: 400,
	},	  
	paperWidgets: {
		paddingTop: theme.spacing(2),
		paddingBottom: theme.spacing(2),
		textAlign: 'center',
	},	  
	paperParameters: {
	  textAlign: 'left',
	  padding: '30px 30px',
	},
	button: {
		marginLeft: theme.spacing(2),
	},
	progress: {
		color: green[500],
	    position: 'fixed',
	    left: 'calc(50% - 50px)',
	  	top: 'calc(50% - 50px)',
	  	width: '100%',
	 	height: '100%',
	  	zIndex: '9999',
	  },
    errorModal: {
		left: '25%',
		top: '25%',
		position: 'absolute',
		width: 400,
		backgroundColor: theme.palette.background.paper,
		border: '2px solid #000',
		boxShadow: theme.shadows[5],
		padding: theme.spacing(2, 4, 3)		  
	  },
	card: {
		minWidth: 275,
	},
	cardTitle: {
		fontSize: 14,
		color: blue[500],
	},	
	successStatus: {
		color: green[500],
	},
	failedStatus: {
		color: red[500],
	}
	
}));

export default function PhalconCrawler () {

	const classes = useStyles();
	
	const [loading, setLoading] = React.useState(false);
	const [urlToCrawl, setURLToCrawl] = React.useState("https://agencyanalytics.com");
	const [isIncorrectURL, setIsIncorrectURL] = React.useState(false);
	
	const [errorModalOpen, setErrorModalOpen] = React.useState(false);
	const [errorModalText, setErrorModalText] = React.useState("");
	
	const [numOfPagesCrawled, setNumOfPagesCrawled] = React.useState(0);
	const [numOfImages, setNumOfImages] = React.useState(0);
	const [numOfIntLinks, setNumOfIntLinks] = React.useState(0);
	const [numOfExtLinks, setNumOfExtLinks] = React.useState(0);
	const [avgPageLoad, setAvgPageLoad] = React.useState(0);
	const [avgWordCount, setAvgWordCount] = React.useState(0);
	const [avgTitleLength, setAvgTitleLength] = React.useState(0);
	const [pageStatuses, setPageStatuses] = React.useState([]);
	
	const renderResults = (data) => {
		setNumOfPagesCrawled(data.numOfPagesCrawled);
		setNumOfImages(data.numOfImages);
		setNumOfIntLinks(data.numOfIntLinks);
		setNumOfExtLinks(data.numOfExtLinks);
		setAvgPageLoad(data.avgPageLoad);
		setAvgWordCount(data.avgWordCount);
		setAvgTitleLength(data.avgTitleLength);
		setPageStatuses(data.pageStatuses);
	};
	
	const onErrorModalClose = () => {
		setErrorModalOpen (false);
		setErrorModalText("");
	};
		
	const onStartCrawlClick = () => {		
		setIsIncorrectURL(false);
		if (!validator.isURL(urlToCrawl)) 
			setIsIncorrectURL(true);
		else {
			setLoading(true);
			
			axios.post("/API/crawl", {
				urlToCrawl: urlToCrawl 
			})	
			.then(function(response){
				setLoading(false);
				if (typeof response == "undefined") {
					setErrorModalText("<p>No data returned</p>Please call support +1 (416) 111-11-11");
					setErrorModalOpen(true);
				} else {
					if (!response.data.success) {
						setErrorModalText("<p>" + response.data.message + "</p>Please call support +1 (416) 111-11-11");
						setErrorModalOpen(true);
					} else 
						renderResults(response.data)
				}
			})
			.catch(function(error){
				setLoading(false);
				setErrorModalText("<p>" + error + "</p>Please call support +1 (416) 111-11-11");
				setErrorModalOpen(true);
			})
		}
	}; 
	
	return (
	<div className={classes.root}>
    	<CssBaseline />
		<AppBar position="static">
			<Toolbar>
				<Typography variant="h6">
					PhalconCrawler
				</Typography>
			</Toolbar>
		</AppBar>
		<Grid container direction="column" spacing={3}>
			{loading && <CircularProgress size={100} className={classes.progress} />}
        	<Grid item xs={12}>
        		<Paper className={classes.paperParameters}>
        			<Grid container direction="row" alignItems="flex-end">
        				<TextField
        	        		id="standard-basic"
        	        		error={isIncorrectURL}	
    	        			className={classes.textField}
        	          		label="Please enter a URL to crawl"
    	          			helperText={isIncorrectURL ? "You seems to have entered a wrong URL" : ""}	
    	          			margin="normal"
    	          			value={urlToCrawl}	
	          				onChange={event => setURLToCrawl(event.target.value)}	
	          			/>
	          			<Button 
	          				color="primary" 
          					className={classes.button}
        					onClick={onStartCrawlClick}
        				>
        	        		Let us crawl!
    	        		</Button>
        	        	<Modal
        	    			aria-labelledby="simple-modal-title"
        	    			aria-describedby="simple-modal-description"
        	    			open={errorModalOpen}
        	    			onClose={onErrorModalClose}
        	    		>
        	    			<div className={classes.errorModal}>
        	    				<h2 id="simple-modal-title">Error happened!</h2>
        	    				<p id="simple-modal-description" dangerouslySetInnerHTML={{ __html: errorModalText }}>
        	    				</p>
        	    			</div>
        	    		</Modal>	        	        		
        	        </Grid>		
        		</Paper>
    		</Grid>
        	<Grid item xs={12}>
        		<Paper className={classes.paperWidgets}>
        			<Grid container direction="row" justify="center" alignItems="center" spacing={3}>
        				<Grid item>
	            			<Card className={classes.card}>
		        				<CardContent>
		        					<Typography className={classes.cardTitle}>
		        						Number of pages crawled
		        					</Typography>
		        					<Typography variant="h3">
		        						{numOfPagesCrawled}
		        					</Typography>
		        				</CardContent>
	        				</Card>
        				</Grid>
        				<Grid item>
	            			<Card className={classes.card}>
		        				<CardContent>
		        					<Typography className={classes.cardTitle}>
		        						Number of unique images
		        					</Typography>
		        					<Typography variant="h3">
		        						{numOfImages}
		        					</Typography>
		        				</CardContent>
	        				</Card>        				
        				</Grid>
        				<Grid item>
	            			<Card className={classes.card}>
		        				<CardContent>
		        					<Typography className={classes.cardTitle}>
		        						Number of unique internal links
		        					</Typography>
		        					<Typography variant="h3">
		        						{numOfIntLinks}
		        					</Typography>
		        				</CardContent>
	        				</Card>          				
        				</Grid>
        				<Grid item>
	            			<Card className={classes.card}>
		        				<CardContent>
		        					<Typography className={classes.cardTitle}>
		        						Number of unique external links
		        					</Typography>
		        					<Typography variant="h3">
		        						{numOfExtLinks}
		        					</Typography>
		        				</CardContent>
	        				</Card>         				
        				</Grid>
        				<Grid item>
	            			<Card className={classes.card}>
		        				<CardContent>
		        					<Typography className={classes.cardTitle}>
		        						Average page load time (s)
		        					</Typography>
		        					<Typography variant="h3">
		        						{avgPageLoad}
		        					</Typography>
		        				</CardContent>
	        				</Card>         				
        				</Grid>
        				<Grid item>
	            			<Card className={classes.card}>
		        				<CardContent>
		        					<Typography className={classes.cardTitle}>
		        						Average word count
		        					</Typography>
		        					<Typography variant="h3">
		        						{avgWordCount}
		        					</Typography>
		        				</CardContent>
	        				</Card>          				
        				</Grid>
        				<Grid item>
	            			<Card className={classes.card}>
		        				<CardContent>
		        					<Typography className={classes.cardTitle}>
		        						Average Title length
		        					</Typography>
		        					<Typography variant="h3">
		        						{avgTitleLength}
		        					</Typography>
		        				</CardContent>
	        				</Card>             				
        				</Grid>
    				</Grid>
        		</Paper>
    		</Grid>
        	<Grid item xs={12}>
    			<Paper className={classes.paperCenter}>
    				<Table aria-label="Statuses of the pages">
    					<TableHead>
    						<TableRow>
	    						<TableCell>
	    							Page URL
	    						</TableCell>
	    						<TableCell align="center">
	    							Status
	    						</TableCell>
								<TableCell>
	    							Extra info
	    						</TableCell>
							</TableRow>	    							
    					</TableHead>
        				<TableBody>
	    		        	{pageStatuses.map(row => (
			        			<TableRow key={row.url}>
			        				<TableCell component="th" scope="row">
	    		                    	{row.url}
	    		                    </TableCell>
	    		                    <TableCell align="center">{row.success ? <Typography className={classes.successStatus}>Success</Typography>:<Typography className={classes.failedStatus}>FAILED</Typography>}</TableCell>
	    		                    <TableCell align="left">{row.message}</TableCell>
		                    	</TableRow>
			          		))}    				
    		        	</TableBody>    					
    				</Table>
    			</Paper>
    		</Grid>
		</Grid>	
	</div>
)
};