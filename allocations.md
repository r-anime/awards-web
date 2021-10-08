
# 2021 /r/anime Awards Jury Allocation Algorithm

Last year, we began publishing the implementation of how we allocate jurors in the Awards in order to increase transparency. That post can be viewed [here](https://gist.github.com/JoseiToAoiTori/56bd10081e5022d51243f2e7a285dfec).

Based on feedback from jurors and Hosts alike, we have changed the algorithm this year to be more simple and better at prioritizing applicant preferences. The first step towards this goal is to change the previous 1 - 5 score system to a preference system. This allows applicants to mark what categories they'd *most* want to be in. Not putting a category in the preference list indicates not wanting it at all. Additionally, we've bumped the number of categories a jurors can be in back up to **5**. However, a maximum of **3 categories is still maintained for the main draft**, meaning additional categories will only be assigned if needed to fill up the category. 

## The Extreme TL;DR

We've maintained the threshold based allocation system introduced last year, but have created a simpler preference system in lieu of RNG pruning. This system aims maintain or even surpass the diversity achieved in last year's jury, while adding some more incentive to aim for a higher score on the application as well as some more clarity to the allocation process.

Preliminary tests with data retrofitted from last year's applications shows that categories achieve a lower average preference ranking (meaning more jurors get categories out of their top preferred categories) as well as an increase of around 15 or so total jurors in the awards that we passed up last year.

## Grading

Hosts will grade the applicant's answer based on the following scale:

**0:** Failed. An answer with this grade cannot get into the category the grade corresponds to under any circumstances. Reserved for complete non-answers or utter incoherence. Examples are blank "aaaaaaaaa" spams or something that doesn't even remotely resemble a point.

**1:** Backup. An answer with this grade will likely not get into the category *except* if the category is missing jurors and no one else wants the category. These answers are technically coherent and maybe have an opinion ("I like this show it is good because it's good") but fail to do any explanation or analysis.

**2:** Accepted. This answer is good enough to be in the awards in general. Answers with this grade will very likely get into the category unless it is popular. These answers are generally fine, answer the question, and raise points with explanations, but don't do any deeper analysis.

**3:** Good. An answer with this grade is almost guaranteed to get into its corresponding category if the applicant has it at a high priority. Exceptions naturally occur with mega popular categories like Anime of the Year. These answers include proper analysis, argue their points well, bring up examples, and generally answer the question fully and satisfactory, but don't say anything extraordinary.

**4:** Exceptional. A stronger guarantee to get into the corresponding category than a 3, and an almost 100% certainty if it's an applicant's #1 priority. These answers go above and beyond what is needed and practically do everything perfectly.

Each answer is graded by 3 Hosts and the average determines the applicant's actual grade. This then translates into how they are allocated by the algorithm.

## The Draft

For each applicant, then, we have the following information: Their category score, their category preference, the maximum amount of categories they want to be in, and whether or not they are willing to fill. The algorithm then does the following:

* Step 1: Pick a category. For this category, produce a list of jurors that

  * Have this category as their #1 preference.

  * Have a score in that category (e.g. their Genre score for Action) above 2.5, corresponding to at minimum two 3s and one 2 from three Hosts.

  * Have not yet reached 3 categories or their maximum amount of wanted categories, whichever is lowest.

* Step 2: Sort the list by score (higher scores first) and immunity points (more on them in the next step).

* Step 3: Put all the Jurors in the list into the category one by one, stopping if the category is full (13 jurors generally and 15 jurors in AOTY). Any juror that *didn't* get in is awarded an immunity point, insuring that they are prioritized over other people with the same score in step 2.

* Step 4: Repeat steps 1 to 3 for every category.

* Step 5: Repeat steps 1 to 4 for the next preference, i.e. #2, then #3, then #4.

This constitutes the **first main draft**. It insures that those that have a category at their highest preference and whose answer was deemed good get into the category. If more people want the category equally, score is the determining factor. If the preference and score is the same, immunity points is the determining factor. As such, the algorithm allocates jurors in such a way that should satisfy as many people as possible. By design, the draft continues until no more jurors can be allocated.

After the first main draft, the **second main draft** occurs. This simply repeats Steps 1 to 5 except the qualifying score is now 1.5, corresponding to at minimum two 2s and one 1 from three Hosts. The effect is that people that score a 2 are allocated *after* those that score a 3 and 4. They will still be considering by their highest preference and will be allocated to the categories they want provided that category is not full.

After the first and second main drafts, we look to categories with less than 11 jurors. We then begin the **fill draft**:

* Step 1: Pick the category with the lowest number of jurors. For this category, produce a list of jurors that

  * Have this category in their preference list.

  * Have a score in that category (e.g. their Genre score for Action) above 1.5, corresponding to at minimum two 2s and one 1 from three Hosts.

  * Have not yet reached 5 categories or their maximum wanted amount of categories, whichever is lowest.

* Step 2: Sort the list by preference and then by score (higher scores first).

* Step 3: Put the jurors into the category one at a time until the category has more jurors than another category.

* Step 4: Repeat steps 1 to 3 until every category has reached 11 jurors or there is no jurors left that want categories.

This makes sure that the category with the lowest amount jurors is filled first and that those that prefer it more get into it first. Note that you can get at most 3 categories in the main draft and the last 2 in the fill draft. In the final step, if there are still categories that are not yet filled, we repeat the fill draft except only up to 7 jurors and with a qualifying score of 1 (we want to make sure that every host thinks the answer is good enough for a 1). This is the **backup fill draft**.

And that's it! If you want to know more about the nitty gritty details you can read the commented code [here](https://github.com/r-anime/awards-web/blob/master/util/allocations.js).