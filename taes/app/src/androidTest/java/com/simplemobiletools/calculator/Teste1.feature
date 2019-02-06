Feature:
  Present the Settings Activity
Scenario:
  Given
    The user is in the Main Activity
  When
    The user selects the menu ("..." more button in TabBar)
    And The user chooses the option "Settings"
  Then
    The information regarding the settings appears in a new activity