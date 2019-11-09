const fs = require('fs');
const parser = require('csv-parse');

function idGetter (arr) {
    const regex = /(?!\/)\d+(?=\/)/gm;
    for (i of arr) {
        while ((m = regex.exec(i[1])) !== null) {
            // This is necessary to avoid infinite loops with zero-width matches
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }
            
            // The result can be accessed through the `m`-variable.
            m.forEach((match) => {
                i[1] = `${match}`;
            });
        }
      }
}

function readCSV (file,callback) {
    fs.readFile(file,'utf-8', (err,data) => {
        if (err) throw err;
        return callback(data);
    });
}

function parseCSV (fileData,callback) {
    parser(fileData, {
        comment: '#',
        skip_empty_lines: true,
    }, function (err,output) {
        if (err) throw err;
        idGetter(output);
        return callback(output);
    });
}

function prepareData(file,callback) {
    readCSV(file,(fileData)=>{
        parseCSV(fileData, (parsedData) => {
            return callback(parsedData);
        })
    });
}

module.exports = {
    prepareData,
}