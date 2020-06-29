/* eslint-disable no-await-in-loop */
const apiApp = require('polka')();
const sequelize = require('../models').sequelize;

// Sequelize models to avoid redundancy
const Answers = sequelize.model('answers');
const Applicants = sequelize.model('applicants');
const Applications = sequelize.model('applications');
const QuestionAnswers = sequelize.model('question_answers');
const QuestionGroups = sequelize.model('question_groups');
const Questions = sequelize.model('questions');
const Scores = sequelize.model('scores');

// eslint-disable-next-line no-unused-vars
const log = require('another-logger');
const {yuuko} = require('../bot/index');
const config = require('../config');