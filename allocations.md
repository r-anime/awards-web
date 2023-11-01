# 2023 /r/anime Awards Jury Allocation Algorithm

This writeup is here to guide potential jurors through the details of how our grading system and allocation algorithm works. 

Last year we attempted to create a drasticailly different application with the focus on making every juror answer a production question, reducing the total number of questions, and analyzing anime not picked by the applicants themselves.

Ultimately we didn't acheive the desired outcome in terms of increasing production focus in the actual awards and the application was considered confusing and obtuse. As a result, we're moving back to a more simple structure albeit with a more freeform approach to the production question.

## General Guide

**If you want to just apply as an Open Juror**, simply don't select any categories and answer any question. Of course, you can answer all of them to maximize the chances of at least one answer being satisfactory.

**If you want to get into a Genre category**, answer the first question.

**If you want to get into a Character category**, answer the second question, making sure you only answer *one* of the subquestions.

**If you want to get into a Production category**, answer the third question. Note that this year you get an overall production grade regardless of what you touched upon in your answer. Thus a stellar answer in Animation or Background Art will also count towards your score for Original Soundtrack and Voice Actor.

**If you want to get into the Opening or Ending category**, answer the fourth question.

**If you want to get into Anime, Movie, or Short of the Year**, answer the first three questions. The fourth is not required.

## Grading

Hosts will grade the applicant's answer based on the following scale:

**1:** Failed. An answer with this grade will not be invited to the Awards. They may be technically coherent and perhaps hold an opinion ("I like this show it is good because it's good") but will fail to do any explanation or analysis.

**2:** Accepted into the Awards. These answers are good enough to be invited as an Open Juror and can participate in juror-related activities. Those receiving this grade are generally fine, answer the question, and raise points with explanations but lack deeper analysis.

**3:** Assigned categories. An answer with this grade is almost guaranteed to get into its corresponding category if the applicant has chosen it as a high priority. However, exceptions naturally occur with mega popular categories like Anime of the Year. These answers contain a proper analysis, several well-argued points, and properly cited examples. They will answer the question to a fully and satisfactory manner but fail to say anything extraordinary.

**4:** Given priority. A stronger guarantee to get into the corresponding category than a 3, and an almost 100% certainty if it's an applicant's number one priority. These answers go above and beyond what is needed in the question and approach a perfect score across the board.

Each answer will be graded by 3 Hosts and the resulting average will determine the applicant's actual grade. This then translates into how they are allocated by the algorithm. Note that you only need one answer with a grade of 2 and above to be accepted as an Open Juror, regardless of if you get into any categories or not.

## The Algorithm

For each applicant, we will have the resulting information: Their category score, their category preference, and the maximum number of categories they want to be in. The algorithm then does the following:

* Step 1: Pick a category. For this category, produce a list of jurors that

  * Have this category as their #1 preference.

  * Have a score in that category (e.g. their Genre score for Action) above 2.2, corresponding to at minimum one 3 and two 2s from three Hosts. For Anime of the Year, this is increased to 2.6, corresponding to two 3s and one 2 from three Hosts.

  * Have not yet reached 5 categories or their maximum amount of wanted categories, whichever is lowest.

* Step 2: Sort the list by score (higher scores first) and immunity points (more on them in the next step).

* Step 3: Put all the Jurors in the list into the category one by one, stopping if the category has 11 jurors. Any juror that *didn't* get in is awarded an immunity point, insuring that they are prioritized over other people with the same score in step 2.

* Step 4: Repeat steps 1 to 3 for every category.

* Step 5: Repeat steps 1 to 4 for the next preference, i.e. #2, then #3, then #4.

And that's it! If you want to know more about the nitty gritty details you can read the commented code [here](https://github.com/r-anime/awards-web/blob/master/util/allocations.js).
