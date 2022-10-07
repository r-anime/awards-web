# 2022 /r/anime Awards Jury Allocation Algorithm

This writeup is here to guide potential jurors through the details of how our grading system and allocation algorithm works. 

Last year we identified several issues and had some goals in mind when creating the new application.
1. Making sure that each juror is able to discuss and analyse production elements in anime, as well as increase the overall juror quality.
2. Reducing the total number of questions while still allowing applicants to pick what questions they want to answer.
3. Focusing more on analysing anime that the applicant does not pick themselves.

With these goals in mind, we have decided on the current application system.

## General Guide
**If you want to just apply as an Open Juror**, simply don't select any categories and answer any question. Of course, you can answer all of them to maximize the chances of at least one answer being satisfactory.

**If you want to get into a Genre or Character category**, answer Questions 2/3 (Genre) or Questions 1/3 (Character). If you answer both, your best answer will be used as your final grade. Though you do not need to focus on any specific aspect, please remember that production aspects still need to be included in your answer.

**If you want to get into a Visual Production category**, answer any of the three questions and focus your response towards visual production. Like above, your best answer will be used if you answer multiple questions.

**If you want to get into the Voice Acting / OPED / Original Soundtrack categories**, answer Questions 1/2/3 respectively. For OPED, you should aim to mention both visual and audio elements although an extremely good analysis of either is also completely fine.

**If you want to get into Anime, Movie, or Short of the Year**, answer all three questions. Please make sure that Visual Production, Voice Acting, and Original Soundtrack are covered.

**If you want to (potentially) get into all categories**, then there is a wide availability of options open to you. You could go for a strong VA answer for Question 1, you could direct your attention to Visual Production and Audio for Question 2, or you could concentrate on OST for Question 3. Alternatively, you could do a Visual and VA focus in Question 1 or a Visual and OST focus in Question 3. Talking about two aspects in one answer won't drag either one down so long as each part stands strong on its own.

Generally speaking, you will have an plenty of options to choose from for the categories you wish to enter into.

## Grading

Hosts will grade the applicant's answer based on the following scale:

**1:** Failed. An answer with this grade will not be invited to the Awards. They may be technically coherent and perhaps hold an opinion ("I like this show it is good because it's good") but will fail to do any explanation or analysis.

**2:** Accepted into the Awards. These answers are good enough to be invited as an Open Juror and can participate in juror-related activities. Those receiving this grade are generally fine, answer the question, and raise points with explanations but lack deeper analysis.

**3:** Assigned categories. An answer with this grade is almost guaranteed to get into its corresponding category if the applicant has chosen it as a high priority. However, exceptions naturally occur with mega popular categories like Anime of the Year. These answers contain a proper analysis, several well-argued points, and properly cited examples. They will answer the question to a fully and satisfactory manner but fail to say anything extraordinary.

**4:** Given priority. A stronger guarantee to get into the corresponding category than a 3, and an almost 100% certainty if it's an applicant's number one priority. These answers go above and beyond what is needed in the question and approach a perfect score across the board.

Each answer will be graded by 5 Hosts and the resulting average will determine the applicant's actual grade. This then translates into how they are allocated by the algorithm. Note that you only need one answer with a grade of 2 and above to be accepted as an Open Juror, regardless of if you get into any categories or not.

## The Algorithm

For each applicant, we will have the resulting information: Their category score, their category preference, the maximum number of categories they want to be in, and whether or not they are willing to fill. The algorithm then does the following:

* Step 1: Pick a category. For this category, produce a list of jurors that

  * Have this category as their #1 preference.

  * Have a score in that category (e.g. their Genre score for Action) above 2.5, corresponding to at minimum two 3s and one 2 from three Hosts.

  * Have not yet reached 5 categories or their maximum amount of wanted categories, whichever is lowest.

* Step 2: Sort the list by score (higher scores first) and immunity points (more on them in the next step).

* Step 3: Put all the Jurors in the list into the category one by one, stopping if the category has 11 jurors. Any juror that *didn't* get in is awarded an immunity point, insuring that they are prioritized over other people with the same score in step 2.

* Step 4: Repeat steps 1 to 3 for every category.

* Step 5: Repeat steps 1 to 4 for the next preference, i.e. #2, then #3, then #4.

This is then repeated for every category.

And that's it! If you want to know more about the nitty gritty details you can read the commented code [here](https://github.com/r-anime/awards-web/blob/master/util/allocations.js).
